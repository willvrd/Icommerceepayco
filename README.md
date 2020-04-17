# asgardcms-icommerceepayco

### Add Except

1. Go to app/http/middleware/VerifyCsrfToken
2. add this:  

	protected $except = [
        '/icommerceepayco/ok'
    ];

### Data Configuration Example



	