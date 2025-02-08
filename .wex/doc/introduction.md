### **Pseudocode Generator**

## **Overview**

The **Pseudocode Generator** is a tool that analyzes PHP code and generates a structured representation (in YAML) of its components, including:
- Classes and their methods
- Functions
- Constants

The generator extracts not only the code structure but also any associated documentation, including PHPDoc comments and inline comments.

---

## **Architecture**

### **Project Structure**

```
src/
├── Generator/
│   ├── AbstractGenerator.php
│   └── PseudocodeGenerator.php    # Main entry point
├── Item/
│   ├── AbstractItem.php           # Base class for all items
│   ├── ClassItem.php              # Handles class extraction
│   ├── ConstantItem.php           # Handles constant extraction
│   └── FunctionItem.php           # Handles function extraction
├── Parser/
│   └── PhpParser.php              # PHP code parsing logic
└── Testing/
    └── Traits/
        └── WithYamlTestCase.php    # YAML-based test support
```

---

### **Main Components**

#### **1. PseudocodeGenerator**
The primary entry point that:
- Receives the PHP code to be analyzed
- Uses **PhpParser** for parsing
- Returns the result in YAML format

#### **2. PhpParser**
Utilizes the **PHP-Parser** library to:
- Parse PHP code into an **AST (Abstract Syntax Tree)**
- Identify different types of elements (classes, functions, constants)
- Extract comments and documentation

#### **3. Items**
Each type of code element (ClassItem, FunctionItem, ConstantItem):
- Inherits from `AbstractItem`
- Implements its own extraction logic via `fromNode()`
- Manages its specific data representation

---

## **Key Features**

### **1. Comment Extraction**
The system processes two types of comments:
- **PHPDoc comments:** Structured documentation with tags (`@param`, `@return`, etc.)
- **Inline comments:** Comments appearing on the same line as the code

### **2. Type Handling**
Supports various PHP types, including:
- **Primitive types** (`int`, `string`, etc.)
- **Composite types** (`array`, `callable`)
- **Nullable types** (`Type|null`)
- **Union types** (`Type1|Type2`)

### **3. Data Structure**
The YAML output follows a consistent structure:

```yaml
items:
  - type: class|function|constant
    name: string
    description: string
    # For classes:
    properties:
      - name: string
        type: string
        description: string
        default: mixed
    methods:
      - name: string
        description: string
        parameters: [...]
        returnType: string
    # For functions:
    parameters:
      - name: string
        type: string
        description: string
    returnType: string
    # For constants:
    value: mixed
```

---

## **Testing**

Tests are organized by item type:

```
tests/
└── Item/
    ├── Class/
    │   ├── resources/            # Example PHP files
    │   └── ClassItemTest.php
    ├── Constant/
    │   ├── resources/
    │   └── ConstantItemTest.php
    └── Function/
        ├── resources/
        └── FunctionItemTest.php
```

Each test case:
1. Loads an example PHP file
2. Converts it into pseudocode
3. Compares it with an expected YAML output

---

## **Usage**

```php
$generator = new PseudocodeGenerator();

// Generate the structured representation
$items = $generator->generateItems($phpCode);

// Or directly output YAML
$yaml = $generator->generatePseudocode($phpCode);
```

---

## **Best Practices**

### **1. Comment Extraction**
- Always check both **PHPDoc** and **inline comments**
- Use `getDocComment()` for PHPDoc
- Use `getInlineComment()` for inline comments

### **2. Type Handling**
- Use `getTypeName()` for consistent type extraction
- Handle edge cases (union types, nullable types)

### **3. Testing**
- One test file per item type
- Clearly named and explicit test cases
- Well-documented example files
