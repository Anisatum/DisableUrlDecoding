## Documentation

The plugin can be activated from the System Settings, or through Javascript. The Javascript settings override the System
Settings. URL-decoding can be disabled for:

- All pages
- Only for specified URL Parameters
- Only for pages matching a Regular Expression, or to groups captured by it

### Disable Decoding For All Pages

To enable through Javascript:

```
_paq.push(['DisableUrlDecoding.doNotDecode']);
```

Simply do not decode the URLs, ever. This option *should* work correctly in most cases, but there are two issues to
consider:

- URL-decoding was originally implemented because of addresses UTF-16 encoded characters. For example a URL like:

```
https://example.com/path/?post_type=H%E4ll%F6
```

Is not compatible with the URL-encoding that is commonly used, and would therefore cause issues down the line. If
standard URL-decoding with the `decodeURIComponent` function fails, `unescape`, which handles UTF-16 encoding, is
applied, and the URL becomes:

```
https://example.com/path/?post_type=Hällö
```

Modern browsers do not use UTF-16 encoding anymore, but if you are still getting such requests you might want to
consider one of the less aggressive options listed below

- The default Matomo logic has become expected behavior. If your website uses addresses like:

```
https://example.com/path%20with%20whitespace/
```

which are stored as

```
https://example.com/path with whitespace/
```

you might not have noticed any issues, since browsers automatically convert whitespaces to `%20`. If you've gathered a
lot of data with decoded URLs, disabling it would cause "new" pages to appear in your reporting, and could have
other downstream effects.

In these cases you might want to keep the old logic for most addresses, and only disable decoding in specific
cases.

### Disable Decoding For Specified URL Parameters

To enable through Javascript:

```
_paq.push(['DisableUrlDecoding.doNotDecode', {disableFor: "params", params: ['q', 'search']}]);
```

This allows you to disable the decoding of specified parameters, in this case `q` and `search`. The URL:

```
https://example.com/path%20with%20whitespace/?q=Heatmap+%26+session+recording&post_type=H%E4ll%F6
```

will be stored as:

```
https://example.com/path with whitespace/?q=Heatmap+%26+session+recording&post_type=Hällö
```

### Disable Decoding For URLs Matching a Regular Expression

To enable through Javascript:

```
_paq.push(['DisableUrlDecoding.doNotDecode', {disableFor: "regexp", regexp: "example\\.com\\/(.*)\\/\\?(q=.*)&"}]);
```

This allows you to disable the decoding of groups captured by the RegExp. The URL:

```
https://example.com/path%20with%20whitespace/?q=Heatmap+%26+session+recording&post_type=H%E4ll%F6
```

will be stored as:

```
https://example.com/path%20with%20whitespace/?q=Heatmap+%26+session+recording&post_type=Hällö
```

Using a Regular Expression with no groups will disable decoding for the entire URL matching it.

### Fallback

If the plugin configuration results in a URL that cannot be handled by `decodeURIComponent`, the old logic will be used
as fallback. For example the configuration:

```
_paq.push(['DisableUrlDecoding.doNotDecode', {disableFor: "regexp", regexp: "example\\.com"}]);
```

applied to the URL:

```
https://example.com/path%20with%20whitespace/?q=Heatmap+%26+session+recording&post_type=H%E4ll%F6
```

would attempt to store the entire URL as is, but because the UTF-16 characters cannot be decoded
with `decodeURIComponent`, the `unescape` function is applied to the entire URL just like it would be usually done by
Matomo. The end result being:

```
https://example.com/path with whitespace/?q=Heatmap+&+session+recording&post_type=Hällö
```

### Re-Enable Decoding Through Javascript

It is also possible to selectively re-enable decoding through Javascript with the `doDecode` method:

```
_paq.push(['DisableUrlDecoding.doDecode'}]);
```