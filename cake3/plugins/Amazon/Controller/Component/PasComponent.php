<?php
Configure::load('aws');

class PasComponent extends Object {

	/**
	 * Available Locales
	 */
	const LOCALE_US = 'us';
	const LOCALE_UK = 'uk';
	const LOCALE_CANADA = 'ca';
	const LOCALE_FRANCE = 'fr';
	const LOCALE_GERMANY = 'de';
	const LOCALE_JAPAN = 'jp';
	
	/**
	 * Property: locale
	 * The Amazon locale to use by default.
	 */
	private $locale;
	
   /**
    * Associate Tag
    */
    private $AssociateTag;
    
    /**
     * Developer Tag
     */
	private $AWSAccessKeyId;

	/**
	 * Amazon supplied secret key
	 */
    private $AWSSecretKey;
    
    /**
     * Server URL which is set by setLocale
     */
    private $AWSEndPoint;

    /**
     * Set the AWS keys required by the Amazon Product Advertising API. Setting
     * them through a method allows them to be set by a simple call which is useful
     * for testing. 
     * @param string $access Your 20 character AWS developer access key. 
     * @param string $secret Your 40 character AWS secret key used for calculating the signature 
     * @param string $associate Your Amazon Associate ID
     */
    protected function setKeys( $access, $secret, $associate ){
      	$this->AWSAccessKeyId = $access;
      	$this->AWSSecretKey = $secret;
      	$this->AssociateTag = $associate;
    }

    /**
	 * initialize the component by reading the Amazon keys from the configuration
	 * @param AppController $controller reference to the containing controller
	 * @param array $settings optional array of settings
	 */
	function initialize(&$controller, $settings = array()) {
		$this->setKeys( Configure::read('Aws.key'), 
						Configure::read('Aws.secret'),
						Configure::read('Aws.assoc')
		);
        $this->setLocale(self::LOCALE_US);
	}

	/**
	 * shutdown
	 * Shutdown the component. Called by the component after render()
	 * @param AppController $controller reference to containing controller
	 */
	function shutdown(&$controller) {
	}
    
    /**
     * Sign the parameters
     * The parameters are signed by calculating a 'Signature' parameter which
     * is added in to the request
     * @param array $params an associative array of Amazon request parameters
     * @return parameter array with a Signature parameter added 
     */
    private function sign( $params ) {
    	if( isset($this->AssociateTag)){
			$params['AssociateTag'] = $this->AssociateTag;
    	}
        $params['AWSAccessKeyId'] = $this->AWSAccessKeyId;   
		$params['Service'] = 'AWSECommerceService';
        // add a Timestamp to the request unless it's already set.
        if( !array_key_exists('Timestamp', $params )){
            $params['Timestamp'] = date("Y-m-d\TH:i:s.000Z");
        }
        // Sort the parameters alphabetically by key
        ksort($params);
        // get the canonical form of the query string
        $canonical = $this->canonicalize($params);
        // construct the data to be signed as specified in the docs
        $stringToSign = 'GET' . "\n" .
                        $this->AWSEndPoint . "\n" .
                        '/onca/xml' . "\n" .
                        $canonical;

        // calculate the signature value and add it to the request.
        $params['Signature'] = base64_encode(hash_hmac("sha256", $stringToSign, $this->AWSSecretKey, True));
        return $params;
    }


    /**
     * Constructs the canonical form of the query string as specified in the docs.
     * @param array $params query parameters sorted alphabetically by key
     * @return string canonical form of the query string
     */
    private function canonicalize($params) {
        $parts = array();
        foreach( $params as $k => $v){
            $x = rawurlencode($k) . '=' . rawurlencode($v);
            array_push($parts, $x );    
        }
        return implode('&',$parts);
    }


    /**
     * Construct a signed URL to request an Amazon REST response
     * @param $par request parameters
     * @return string canonical form of the Amazon QueryURL
     */
	private function getAmazonURL( $operation, $params ){
		$params['Operation'] = $operation;
		return 'http://' . $this->AWSEndPoint . '/onca/xml' . 
      			'?' . $this->canonicalize($this->sign($params));
	}

	/**
	 * Set the locale to use
	 * @param String $locale LOCALE_CANADA, LOCALE_FRANCE, LOCALE_GERMANY, LOCALE_JAPAN, LOCALE_UK or LOCALE_US
	 */
	public function setLocale($locale = null)
	{
		$this->locale = $locale;
		// Determine the hostname
		switch ($locale)
		{
			// United Kingdom
			case self::LOCALE_UK:
				$this->AWSEndPoint = 'ecs.amazonaws.co.uk';
				break;

			// Canada
			case self::LOCALE_CANADA:
				$this->AWSEndPoint = 'ecs.amazonaws.ca';
				break;

			// France
			case self::LOCALE_FRANCE:
				$this->AWSEndPoint = 'ecs.amazonaws.fr';
				break;

			// Germany
			case self::LOCALE_GERMANY:
				$this->AWSEndPoint = 'ecs.amazonaws.de';
				break;

			// Japan
			case self::LOCALE_JAPAN:
				$this->AWSEndPoint = 'ecs.amazonaws.jp';
				break;

			// Default to United States
			default:
				$this->AWSEndPoint = 'ecs.amazonaws.com';
				break;
		}
	}
	
	/**
	 * Lookup a specific Item
	 * @param array $parameters
	 * @link http://docs.amazonwebservices.com/AWSECommerceService/2010-11-01/DG/
	 * @return string URL to request the item 
	 */
	public function itemLookup( $parameters ) {
		return $this->getAmazonURL( 'ItemLookup', $parameters );
	}
	
	/**
	 * Search for items
	 * @param array $parameters
	 * @link http://docs.amazonwebservices.com/AWSECommerceService/2010-11-01/DG/
	 * @return string URL to request the item 
	 */
	public function itemSearch( $parameters ) {
		return $this->getAmazonURL( 'ItemSearch', $parameters );
	}
	
	/**
	 * Lookup a seller listing
	 * @param array $parameters
	 * @link http://docs.amazonwebservices.com/AWSECommerceService/2010-11-01/DG/
	 * @return string URL to request the item 
	 */
	public function sellerListingLookup( $parameters ){
		return $this->getAmazonURL( 'SellerListingLookup', $parameters );
	}

	/**
	 * Search for a seller listing
	 * @param array $parameters
	 * @link http://docs.amazonwebservices.com/AWSECommerceService/2010-11-01/DG/
	 * @return string URL to request the item 
	 */
	public function sellerListingSearch( $parameters ){
		return $this->getAmazonURL( 'SellerListingSearch', $parameters );
	}

	/**
	 * Lookup a seller
	 * @param array $parameters
	 * @link http://docs.amazonwebservices.com/AWSECommerceService/2010-11-01/DG/
	 * @return string URL to request the item 
	 */
	public function sellerLookup( $parameters ){
		return $this->getAmazonURL( 'SellerLookup', $parameters );
	}

	/**
	 * Lookup similar items
	 * @param array $parameters
	 * @link http://docs.amazonwebservices.com/AWSECommerceService/2010-11-01/DG/
	 * @return string URL to request the item 
	 */
	public function similarityLookup( $parameters ){
		return $this->getAmazonURL( 'SimilarityLookup', $parameters );
	}
	
	
	/**
	 * Lookup a browse node
	 * @param array $parameters
	 * @link http://docs.amazonwebservices.com/AWSECommerceService/2010-11-01/DG/
	 * @return string URL to request the item 
	 */
	public function browseNodeLookup( $parameters ) {
		return $this->getAmazonURL( 'BrowseNodeLookup', $parameters );
	}
	
	/*%******************************************************************************************%*/
	// CART METHODS

	/**
	 * Add items to an existing cart
	 * @param string $cart_id  Alphanumeric token returned by <cartCreate()> 
	 * @param string $hmac Encrypted alphanumeric access token returned by <cartCreate()>
	 * @param mixed $offers Either a string containing the Offer ID to add or an associative array where the Offer ID is the key and the quantity is the value. An offer listing ID is an alphanumeric token that uniquely identifies an item. Use the OfferListingId instead of an item's ASIN to add the item to the cart.
	 * @param array $parameters Associative array of parameters
	 * @link http://docs.amazonwebservices.com/AWSECommerceService/2010-11-01/DG/
	 * @return string URL to request the item 
	 */
	public function cartAdd($cart_id, $hmac, $offers, $parameters = array()){
		$parameters['CartId'] = $cart_id;
		$parameters['HMAC'] = $hmac;

		if (is_array($offers)) {
			$count = 1;
			foreach ($offers as $offer => $quantity) {
				$parameters['Item.' . $count . '.OfferListingId'] = $offer;
				$parameters['Item.' . $count . '.Quantity'] = $quantity;
				$count++;
			}
		} else {
			$parameters['Item.1.OfferListingId'] = $offers;
			$parameters['Item.1.Quantity'] = 1;
		}

		return $this->getAmazonURL('CartAdd', $parameters);
	}

	/**
	 * Clear a cart
	 * @param string $cart_id  Alphanumeric token returned by <cartCreate()> 
	 * @param string $hmac Encrypted alphanumeric access token returned by <cartCreate()>
	 * @param array $parameters Associative array of parameters
	 * @link http://docs.amazonwebservices.com/AWSECommerceService/2010-11-01/DG/
	 * @return string URL to request the item 
	 */
	public function cartClear($cart_id, $hmac, $parameters = array()){
		$parameters['CartId'] = $cart_id;
		$parameters['HMAC'] = $hmac;
		return $this->getAmazonURL('CartClear', $parameters);
	}

	/**
	 * Create a cart
	 * @param mixed $offers Either a string containing the Offer ID to add or an associative array where the Offer ID is the key and the quantity is the value. An offer listing ID is an alphanumeric token that uniquely identifies an item. Use the OfferListingId instead of an item's ASIN to add the item to the cart.
	 * @param array $parameters Associative array of parameters
	 * @link http://docs.amazonwebservices.com/AWSECommerceService/2010-11-01/DG/
	 * @return string URL to request the item 
	 */
	public function cartCreate($offers, $parameters = array()){
		if (is_array($offers)){
			$count = 1;
			foreach ($offers as $offer => $quantity){
				$parameters['Item.' . $count . '.OfferListingId'] = $offer;
				$parameters['Item.' . $count . '.Quantity'] = $quantity;
				$count++;
			}
		} else {
			$parameters['Item.1.OfferListingId'] = $offers;
			$parameters['Item.1.Quantity'] = 1;
		}
		return $this->getAmazonURL('CartCreate', $parameters);
	}

	/**
	 * Retrieve a cart
	 * @param string $cart_id  Alphanumeric token returned by <cartCreate()> 
	 * @param string $hmac Encrypted alphanumeric access token returned by <cartCreate()>
	 * @param array $parameters Associative array of parameters
	 * @link http://docs.amazonwebservices.com/AWSECommerceService/2010-11-01/DG/
	 * @return string URL to request the item 
	 */
	public function cartGet($cart_id, $hmac, $parameters = array()) {
		$parameters['CartId'] = $cart_id;
		$parameters['HMAC'] = $hmac;
		return $this->getAmazonURL('CartGet', $parameters);
	}

	/**
	 * Modify item quantities of cart items
	 * @param string $cart_id  Alphanumeric token returned by <cartCreate()> 
	 * @param string $hmac Encrypted alphanumeric access token returned by <cartCreate()>
	 * @param array $offers Associative array that specifies an item to be modified in the cart where N is a positive integer between 1 and 10, inclusive. Up to ten items can be modified at a time. CartItemId is neither an ASIN nor an OfferListingId. It is, instead, an alphanumeric token returned by <cart_create()> and <cart_add()>. This parameter is used in conjunction with Item.N.Quantity to modify the number of items in a cart. Also, instead of adjusting the quantity, you can set 'SaveForLater' or 'MoveToCart' as actions instead.
	 * @param array $parameters Associative array of parameters
	 * @link http://docs.amazonwebservices.com/AWSECommerceService/2010-11-01/DG/
	 * @return string URL to request the item 
	 */
	public function cartModify($cart_id, $hmac, $offers, $parameters = array()){
		$parameters['CartId'] = $cart_id;
		$parameters['HMAC'] = $hmac;

		$count = 1;
		foreach ($offers as $offer => $quantity){
			$action = is_numeric($quantity) ? 'Quantity' : 'Action';
			$parameters['Item.' . $count . '.CartItemId'] = $offer;
			$parameters['Item.' . $count . '.' . $action] = $quantity;
			$count++;
		}
		return $this->getAmazonURL('CartModify', $parameters);
	}
	
}
?>