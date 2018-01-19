<?php
App::import('Component', 'Amazon.Pas');
App::import('Controller', 'App');
App::import('Lib', 'ThrottledRequest');

class TestController extends AppController {
    var $name = 'Posts';
    var $autoRender = false;
     
    function redirect($url, $status = null, $exit = true) {
        $this->redirectUrl = $url;
    }
 
    function render($action = null, $layout = null, $file = null) {
        $this->renderedAction = $action;
    }
 
    function _stop($status = 0) {
        $this->stopped = $status;
    }
}

/**
 * Overridden PasComponent so we can set the Amazon keys to known values for testing
 * @author steve
 *
 */
class MypasComponent extends PasComponent {
	
	function initialize(&$controller, $settings = array()) {
		$this->setKeys('12345678901234567890', '1234567890123456789012345678901234567890', 'XX00-11');
    	$this->setLocale(PasComponent::LOCALE_UK);		
	}
}

class PasTestCase extends CakeTestCase {

	function testItemLookup() {
		$component = new MypasComponent(); 
		$controller = new TestController(); 

		$component->initialize($controller); 
      	$params = array( 
      		'Condition' => 'All',
			'IdType' => 'ASIN',
      		'ItemId' => '1904034535',
      		'MerchantId' => 'Amazon',
			'ResponseGroup' => 'Medium',
      		'sort' => 'salesrank',
			'Timestamp' => '2011-03-03T16:05:49.0000'
      	);
		$url = $component->itemLookup($params);
		$component->shutdown($controller);
		
		$expected = 'http://ecs.amazonaws.co.uk/onca/xml' .
			'?AWSAccessKeyId=12345678901234567890' .
			'&AssociateTag=XX00-11' .
			'&Condition=All' .
			'&IdType=ASIN' .
			'&ItemId=1904034535' .
			'&MerchantId=Amazon' .
			'&Operation=ItemLookup' .
			'&ResponseGroup=Medium' .
		 	'&Service=AWSECommerceService' .
			'&Timestamp=2011-03-03T16%3A05%3A49.0000' .
			'&sort=salesrank' .
			'&Signature=RLh0auSeFqH7XfVjsZtfGP6B3gZmjx5W3kAZOoR5LoE%3D';
		$this->assertEqual( $url, $expected );
	}

	function testItemSearch() {
		$component = new MypasComponent(); 
		$controller = new TestController(); 

		$component->initialize($controller); 
      	$params = array( 
			'SearchIndex'=>'Books',
			'Keywords'=>'harry+potter',
      		'ResponseGroup'=>'Large',
      		'Timestamp' => '2011-03-03T16:05:49.0000'
      	);
		$url = $component->itemSearch($params);
		$component->shutdown($controller);
		
		$expected = 'http://ecs.amazonaws.co.uk/onca/xml' .
			'?AWSAccessKeyId=12345678901234567890' .
			'&AssociateTag=XX00-11' .
			'&Keywords=harry%2Bpotter' .
			'&Operation=ItemSearch' .
			'&ResponseGroup=Large' .
			'&SearchIndex=Books' .
			'&Service=AWSECommerceService' .
			'&Timestamp=2011-03-03T16%3A05%3A49.0000' .
			'&Signature=wbrOtL5qVwOO4hItE%2FvJfFMOXzQdMGJHXchDue9DPgs%3D';
		$this->assertEqual( $url, $expected );
	}
	
	function testBrowseNodeLookup() {
		$component = new MypasComponent();
		$controller = new TestController();
		
		$component->initialize($controller);
		$params = array(
			'BrowseNodeId' => '163357',
			'ResponseGroup' => 'NewReleases',
      		'Timestamp' => '2011-03-03T16:05:49.0000'
		);
		$url = $component->browseNodeLookup($params);
		$component->shutdown($controller);
		
		$expected = 'http://ecs.amazonaws.co.uk/onca/xml' .
			'?AWSAccessKeyId=12345678901234567890' .
			'&AssociateTag=XX00-11' .
			'&BrowseNodeId=163357' .
			'&Operation=BrowseNodeLookup' .
			'&ResponseGroup=NewReleases' .
			'&Service=AWSECommerceService' .
			'&Timestamp=2011-03-03T16%3A05%3A49.0000' .
			'&Signature=ptAPnju4tE87Cw80YTXn7AUckcHechTExUCa4Ve5RC0%3D';
		$this->assertEqual( $url, $expected );
	}
	
	function testSimilarityLookup() {
		$component = new MypasComponent();
		$controller = new TestController();
		
		$component->initialize($controller);
		$params = array(
      		'ItemId' => '1904034535',
      		'Timestamp' => '2011-03-03T16:05:49.0000'
		);
		$url = $component->similarityLookup($params);
		$component->shutdown($controller);
		
		$expected = 'http://ecs.amazonaws.co.uk/onca/xml' .
			'?AWSAccessKeyId=12345678901234567890' .
			'&AssociateTag=XX00-11' .
      		'&ItemId=1904034535' .
			'&Operation=SimilarityLookup' .
			'&Service=AWSECommerceService' .
			'&Timestamp=2011-03-03T16%3A05%3A49.0000' .
			'&Signature=TxajmOQRK8NjnRR4smpj%2BKCTlSIMfl46HIOUmMz3MhY%3D';
		$this->assertEqual( $url, $expected );
	}
	
	function testSellerLookup() {
		$component = new MypasComponent();
		$controller = new TestController();
		
		$component->initialize($controller);
		$params = array(
      		'SellerId' => '1904034535',
			'FeedbackPage' => '2',
      		'Timestamp' => '2011-03-03T16:05:49.0000'
		);
		$url = $component->sellerLookup($params);
		$component->shutdown($controller);
		
		$expected = 'http://ecs.amazonaws.co.uk/onca/xml' .
			'?AWSAccessKeyId=12345678901234567890' .
			'&AssociateTag=XX00-11' .
			'&FeedbackPage=2' .
			'&Operation=SellerLookup' .
      		'&SellerId=1904034535' .
			'&Service=AWSECommerceService' .
			'&Timestamp=2011-03-03T16%3A05%3A49.0000' .
			'&Signature=J%2B15N%2FBrTvuOBPmitwZmA4zyixP15NswUie%2B6qKhtio%3D';
		$this->assertEqual( $url, $expected );
	}

	function testSellerListingSearch() {
		$component = new MypasComponent();
		$controller = new TestController();
		
		$component->initialize($controller);
		$params = array(
      		'SellerId' => '1904034535',
			'ListingPage' => '2',
			'OfferStatus' => 'Open',
			'Sort' => '+price',
			'Keywords' => 'thriller',
      		'Timestamp' => '2011-03-03T16:05:49.0000'
		);
		$url = $component->sellerListingSearch($params);
		$component->shutdown($controller);
		
		$expected = 'http://ecs.amazonaws.co.uk/onca/xml' .
			'?AWSAccessKeyId=12345678901234567890' .
			'&AssociateTag=XX00-11' .
			'&Keywords=thriller' .
			'&ListingPage=2' .
			'&OfferStatus=Open' .
			'&Operation=SellerListingSearch' .
      		'&SellerId=1904034535' .
			'&Service=AWSECommerceService' .
			'&Sort=%2Bprice' .
			'&Timestamp=2011-03-03T16%3A05%3A49.0000' .
			'&Signature=4yPkGdgrGx41xg38YCddlmVIDLMVvzMnycwz0CygVvA%3D';
		$this->assertEqual( $url, $expected );
	}

	function testSellerListingLookup() {
		$component = new MypasComponent();
		$controller = new TestController();
		
		$component->initialize($controller);
		$params = array(
      		'Id' => '1904034535',
			'IdType' => 'ASIN',
			'SellerId' => 'XXXYYY',
			'ResponseGroup' => 'SellerListing',
      		'Timestamp' => '2011-03-03T16:05:49.0000'
		);
		$url = $component->sellerListingLookup($params);
		$component->shutdown($controller);
		
		$expected = 'http://ecs.amazonaws.co.uk/onca/xml' .
			'?AWSAccessKeyId=12345678901234567890' .
			'&AssociateTag=XX00-11' .
      		'&Id=1904034535' .
			'&IdType=ASIN' .
			'&Operation=SellerListingLookup' .
			'&ResponseGroup=SellerListing' .
			'&SellerId=XXXYYY' .
			'&Service=AWSECommerceService' .
			'&Timestamp=2011-03-03T16%3A05%3A49.0000' .
			'&Signature=6b7O2ecqIxr%2FKeTg6rglGrH8RWUZPrn4Hn77s4ijRdk%3D';
		$this->assertEqual( $url, $expected );
	}

	function testCartAdd() {
		$component = new MypasComponent();
		$controller = new TestController();
		
		$component->initialize($controller);
		$params = array(
      		'Id' => '1904034535',
			'IdType' => 'ASIN',
			'SellerId' => 'XXXYYY',
			'ResponseGroup' => 'SellerListing',
      		'Timestamp' => '2011-03-03T16:05:49.0000'
		);
		$offers = array(
			'ListingId0001' => 3,
			'ListingId0002' => 1,
			'ListingId0003' => 2
		);
		$url = $component->cartAdd(1, 'HMAC1234', $offers, $params);
		$component->shutdown($controller);
		
		CakeLog::write('debug',$url);
		$expected = 'http://ecs.amazonaws.co.uk/onca/xml' .
			'?AWSAccessKeyId=12345678901234567890' .
			'&AssociateTag=XX00-11' .
			'&CartId=1' .
			'&HMAC=HMAC1234' .
			'&Id=1904034535' .
			'&IdType=ASIN' .
			'&Item.1.OfferListingId=ListingId0001' .
			'&Item.1.Quantity=3' .
			'&Item.2.OfferListingId=ListingId0002' .
			'&Item.2.Quantity=1' .
			'&Item.3.OfferListingId=ListingId0003' .
			'&Item.3.Quantity=2' .
			'&Operation=CartAdd' .
			'&ResponseGroup=SellerListing' .
			'&SellerId=XXXYYY' .
			'&Service=AWSECommerceService' .
			'&Timestamp=2011-03-03T16%3A05%3A49.0000' .
			'&Signature=ucxrID%2FCpkdpIee82a81xY9naCug1m1raRl3ZoPVSKI%3D';
		$this->assertEqual( $url, $expected );
	}
}
 