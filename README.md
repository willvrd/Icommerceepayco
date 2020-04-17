# asgardcms-icommerceepayco

### Add Except

1. Go to app/http/middleware/VerifyCsrfToken
2. add this:  

	protected $except = [
        '/icommerceepayco/ok'
    ];

### Data Configuration Example

- publicKey: 
    491d6a0b6e992cf924edd8d3d088aff1
