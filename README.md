# HMAC (Hash-based Message Authentication Code)

HMAC(K, m) =  hash(K1 + hash(K2 + m))

| Symbol | Description |
| --- | --- |
| `K` | Secret key |
| `m` | Message |
| `hash` | Hash function chosen |
| `K1`, `K2` | Secret keys derived from the original `K` |
| `+` | String concatenation |

## How to use
```
php guard.php [option] [dir]
```

**[dir]**: Directory to be guarded.

**[option]**:

Option | Similar | Description
--- | --- | ---
`-h` | `--help` | Show help.
`-i` |  | Starts the guard of specified directory in [dir].
`-t` |  | Tracking of the specified directory in [dir].
`-d` |  | Disables the guard of specified directory in [dir].
