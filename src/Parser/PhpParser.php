<?php

namespace Wexample\Pseudocode\Parser;

use PhpParser\Lexer\Emulative;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use Wexample\Pseudocode\Item\ClassItem;
use Wexample\Pseudocode\Item\ConstantItem;
use Wexample\Pseudocode\Item\FunctionItem;

class PhpParser extends NodeVisitorAbstract
{
    private array $items = [];
    // On collecte tous les commentaires vus dans l'AST
    private array $allComments = [];
    // On stocke les nœuds correspondant à des appels à define()
    private array $defineNodes = [];

    public function parse(string $code): array
    {
        $lexer = new Emulative([
            'usedAttributes' => [
                'comments',
                'startLine',
                'endLine',
                'startFilePos',
                'endFilePos'
            ]
        ]);
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7, $lexer);
        $ast = $parser->parse($code);

        $traverser = new NodeTraverser();
        // Pour que chaque nœud reçoive un attribut "parent"
        $traverser->addVisitor(new ParentConnectingVisitor());
        $traverser->addVisitor($this);
        $traverser->traverse($ast);

        // Une fois le parcours terminé, on affecte les commentaires inline aux define() en fonction de leur position.
        $this->assignInlineCommentsToDefines();

        // Retourne tous les items (constantes, classes, fonctions, etc.)
        return $this->items;
    }

    /**
     * Pour chaque commentaire inline (commençant par // ou #) collecté,
     * on cherche le nœud define() dont la position de fin est immédiatement
     * précédée par le commentaire.
     */
    protected function assignInlineCommentsToDefines(): void
    {
        // Filtrer uniquement les commentaires inline (ignorer les commentaires multi-lignes ou PHPDoc)
        $inlineComments = array_filter($this->allComments, function($comment) {
            $text = $comment->getText();
            return (strpos($text, '//') === 0 || strpos($text, '#') === 0);
        });

        // Trie des commentaires par leur position de début (croissant)
        usort($inlineComments, function($a, $b) {
            return $a->getStartFilePos() - $b->getStartFilePos();
        });

        // Trie des nœuds define par leur position de fin (croissant)
        usort($this->defineNodes, function(Node $a, Node $b) {
            return $a->getAttribute('endFilePos') - $b->getAttribute('endFilePos');
        });

        // On va associer pour chaque nœud define le commentaire inline le plus proche le précédant.
        $assignedComments = [];
        foreach ($inlineComments as $comment) {
            $commentStart = $comment->getStartFilePos();
            $closestDefine = null;
            $maxEnd = -1;
            // Parcourt de tous les nœuds define pour trouver celui dont la fin est la plus proche, mais inférieure à la position du commentaire
            foreach ($this->defineNodes as $defineNode) {
                $defineEnd = $defineNode->getAttribute('endFilePos');
                if ($defineEnd < $commentStart && $defineEnd > $maxEnd) {
                    $maxEnd = $defineEnd;
                    $closestDefine = $defineNode;
                }
            }
            if ($closestDefine !== null) {
                $hash = spl_object_hash($closestDefine);
                // N'assigne qu'un seul commentaire par nœud
                if (!isset($assignedComments[$hash])) {
                    $text = $comment->getText();
                    if (strpos($text, '//') === 0) {
                        $assignedComments[$hash] = trim(substr($text, 2));
                    } elseif (strpos($text, '#') === 0) {
                        $assignedComments[$hash] = trim(substr($text, 1));
                    }
                }
            }
        }

        // Crée les items de constantes en utilisant le commentaire assigné (s'il existe)
        foreach ($this->defineNodes as $defineNode) {
            $hash = spl_object_hash($defineNode);
            $inlineComment = $assignedComments[$hash] ?? null;
            $this->items[] = ConstantItem::fromNode($defineNode, $inlineComment);
        }
    }

    /**
     * Pendant le parcours, on collecte les commentaires et on stocke
     * les nœuds define() pour traitement ultérieur.
     */
    public function enterNode(Node $node)
    {
        // Collecte des commentaires attachés à ce nœud
        $comments = $node->getAttribute('comments', []);
        foreach ($comments as $comment) {
            $pos = $comment->getStartFilePos();
            $this->allComments[$pos] = $comment;
        }

        // Pour les classes et fonctions, on peut créer immédiatement les items
        if ($node instanceof Node\Stmt\Class_) {
            $this->items[] = ClassItem::fromNode($node);
        } elseif ($node instanceof Node\Stmt\Function_) {
            $this->items[] = FunctionItem::fromNode($node);
        } elseif (
            $node instanceof Node\Expr\FuncCall &&
            $node->name->toString() === 'define'
        ) {
            // On stocke le nœud define() pour le traiter après
            $this->defineNodes[] = $node;
        }
    }

    // Méthodes utilitaires (getTypeName, getDocComment, etc.) inchangées

    private function getTypeName($type): string
    {
        if ($type instanceof Node\Name) {
            return $type->toString();
        }
        if ($type instanceof Node\Identifier) {
            return $type->toString();
        }
        return 'mixed';
    }

    private function getDocComment(Node $node): ?string
    {
        if (!$node->getDocComment()) {
            return null;
        }
        $docComment = $node->getDocComment()->getText();
        if (preg_match('/\*\s+([^@\n]+)/', $docComment, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }
}
