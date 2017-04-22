# yii2-auth-filter

Auth filter implementation for 0xhexspeak oauth2 server.

How to use?

1) In controller where you need access check just extend first from **Oxhexspeak\OauthFilter\Controllers\RestController**.

```
class CountryController extends Oxhexspeak\OauthFilter\Controllers\RestController
{
}
```
2) In your .env.dist specify a url to the oauth2 server or set environment variable.

```
AUTH_URL = <your_ouath_server_endpoint>
```

or

```
export AUTH_URL = "<your_ouath_server_endpoint>"
```

