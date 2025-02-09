### **Pseudocode Generator**

## **Overview**

The **Pseudocode Generator** is a tool that analyzes PHP code and generates a structured representation (in YAML) of its
components, including:

- Classes and their methods
- Functions
- Constants

The generator extracts not only the code structure but also any associated documentation, including PHPDoc comments and
inline comments.

## **Key Technical Details**

Le but du peudocode est de tirer des informations générales sur du code sans spécifier la technologie employée derrière.
Une configuration extraite sur une entité doctrine pourrait être employée pour produire du code avec Prisma ou SQL
Alchemy.

### Configuration Objects

The system uses a chain of configuration objects to build the final representation:

- Each code element (class, property, function) has its own configuration class
- Configuration objects can reference their parent context through the `parentConfig` parameter
- The final output structure is built by recursively calling `toConfig()` on these objects

### Ajouter une configuration

- Définir la notation à prendre en compte, par exemple :

```php
#[ORM\Id]
```

- Définir la notation qui en résultera en pseudocode, par exemple:

```yaml
item:
  - name: ...
    database:
      primary: true
```

- Ajouter un fichier de configuration dans `src/Config` qui servira a 
  - parser le code pour en faire du pseudocode
  - récupérer le pseudocode et en faire du code
