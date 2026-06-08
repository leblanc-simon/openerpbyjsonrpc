# Building queries

Odoo searches are expressed as **domains**: lists of conditions, each a triplet
`[field, operator, value]`. You can pass these raw arrays to
[`Model::search()`](working-with-records.md#searching-records), or build them
with the fluent `Criteria` class (`OpenErpByJsonRpc\Criteria`).

## Raw domains

A domain is an array of triplets. Multiple triplets are combined with a logical
**AND**:

```php
// login = 'admin' AND active = true
$model->search('res.users', [
    ['login', '=', 'admin'],
    ['active', '=', true],
], ['id', 'login']);
```

Each triplet is `[string $field, string $operator, mixed $value]`. This is the
native Odoo format; `Criteria` simply gives you a typed, discoverable way to
produce it.

## The `Criteria` builder

`Criteria` provides one method per operator. Every method returns the same
instance, so calls can be chained. Conditions are accumulated in order and
combined with **AND**.

```php
use OpenErpByJsonRpc\Criteria;

$criteria = (new Criteria())
    ->equal('is_company', true)
    ->ilike('name', 'acme')
    ->greaterThan('id', 10);

$model->search('res.partner', $criteria, ['id', 'name']);
```

You can also start from the static factory:

```php
$criteria = Criteria::create()
    ->equal('login', 'admin')
    ->equal('active', true);
```

### Available operators

| Method | Operator | Typical use |
|--------|----------|-------------|
| `equal($field, $value)` | `=` | Exact match. |
| `notEqual($field, $value)` | `!=` | Exclude a value. |
| `lessThan($field, $value)` | `<` | Strictly less than. |
| `lessEqual($field, $value)` | `<=` | Less than or equal. |
| `greaterThan($field, $value)` | `>` | Strictly greater than. |
| `greaterEqual($field, $value)` | `>=` | Greater than or equal. |
| `like($field, $value)` | `like` | Case-sensitive pattern match. |
| `ilike($field, $value)` | `ilike` | Case-insensitive pattern match. |
| `in($field, $value)` | `in` | Value is in a list. |
| `notIn($field, $value)` | `not in` | Value is not in a list. |
| `add($field, $value, $compare)` | custom | Add a condition with an explicit operator. |

`$value` is `mixed`, so you can pass scalars or arrays as appropriate (an array
makes sense with `in` / `not in`):

```php
$criteria = (new Criteria())->in('state', ['draft', 'sent']);
```

### Operator constants

The operator strings are also exposed as class constants, handy when calling
`add()` directly:

```php
Criteria::EQUAL          // '='
Criteria::NOT_EQUAL      // '!='
Criteria::LESS_THAN      // '<'
Criteria::LESS_EQUAL     // '<='
Criteria::GREATER_THAN   // '>'
Criteria::GREATER_EQUAL  // '>='
Criteria::LIKE           // 'like'
Criteria::ILIKE          // 'ilike'
Criteria::IN             // 'in'
Criteria::NOT_IN         // 'not in'
```

```php
$criteria = (new Criteria())->add('priority', '1', Criteria::GREATER_EQUAL);
```

### Inspecting the generated domain

`get()` returns the raw domain array that will be sent to the server — useful for
debugging or for passing the result to a low-level call:

```php
$criteria = (new Criteria())->equal('login', 'admin')->equal('active', true);

$criteria->get();
// [
//     ['login', '=', 'admin'],
//     ['active', '=', true],
// ]
```

## Combining conditions

`Criteria` always joins conditions with **AND**. If you need an **OR** or more
complex prefix-notation domains (Odoo's polish-notation `'|'` / `'!'`
operators), build the domain array yourself and pass it directly to
`Model::search()`:

```php
// (login = 'admin') OR (login = 'demo')
$model->search('res.users', [
    '|',
    ['login', '=', 'admin'],
    ['login', '=', 'demo'],
], ['id', 'login']);
```
