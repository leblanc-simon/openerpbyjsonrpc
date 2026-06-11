# Sessions

The `Session` client (`OpenErpByJsonRpc\Client\Session`) exposes the
session-level endpoints of the Odoo web API: reading session information, listing
languages and modules, and changing the current user's password.

Create it from a connected `OpenERP` instance:

```php
use OpenErpByJsonRpc\Client\Session;

$session = new Session($odoo);
```

## `getInfos()` — session information

```php
public function getInfos(): array
```

Returns an associative array describing the current session. When the client is
**not** logged in, it returns a minimal payload (`['uid' => null]`) instead of
failing. When logged in, it includes the user id, the user context, the
database, and — importantly — the **session id**.

```php
$infos = $session->getInfos();

$userId    = $infos['uid'];         // e.g. 2 (null when not logged in)
$sessionId = $infos['session_id'];  // persist this to reconnect later
```

> Since Odoo 15 the session id is no longer part of the raw server response (the
> session is tracked through the cookie). The library fills `session_id` back in
> for you, so `getInfos()['session_id']` is always available when logged in.

The `session_id` returned here is exactly the value you can later pass to
[`OpenERP::reconnectOrLogin($sessionId)`](connection-and-authentication.md#reconnecting--reconnectorloginsessionid)
to restore the session without re-sending credentials.

## `getLangList()` — available languages

```php
public function getLangList(): array
```

Returns the list of languages installed on the server. Each entry is a
`[code, label]` pair.

```php
$languages = $session->getLangList();
// [ ['fr_FR', 'French / Français'], ['en_US', 'English (US)'], ... ]
```

## `getModules()` — installed modules

```php
public function getModules(): array
```

Returns the list of module technical names available in the current session.

```php
$modules = $session->getModules();
// ['web', 'base', 'mail', ...]
```

## `changePassword()` — change the current password

```php
public function changePassword(string $oldPassword, string $newPassword): array
```

Changes the password of the **currently authenticated** user and returns
`['new_password' => '<the new password>']` on success.

```php
$session->changePassword('admin', 'a-much-stronger-password');
// ['new_password' => 'a-much-stronger-password']
```

> After changing the password, any new authentication must use the new value.
> Update the password you pass to `OpenERP::setPassword()` accordingly.

## Method summary

| Method | Returns | Description |
|--------|---------|-------------|
| `getInfos()` | `array` | Session details, including `uid` and `session_id`. |
| `getLangList()` | `array` | Installed languages as `[code, label]` pairs. |
| `getModules()` | `array` | Available module technical names. |
| `changePassword($old, $new)` | `array` | `['new_password' => $new]` on success. |
