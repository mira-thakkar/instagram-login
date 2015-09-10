<?php
class Instagram {

//The API base URL
const API_URL = 'https://api.instagram.com/v1/';

//The API OAuth URL
const API_OAUTH_URL = 'https://api.instagram.com/oauth/authorize';

//The OAuth token URL
const API_OAUTH_TOKEN_URL = 'https://api.instagram.com/oauth/access_token';

private $_apikey;
private $_apisecret;
private $_callbackurl;
private $_accesstoken;

//set scope for acquiring permission
private $_scopes = array('basic', 'likes', 'comments', 'relationships');

public function __construct($config) {
if (true === is_array($config)) {
     //if you want to access user data
     $this->setApiKey($config['apiKey']);
     $this->setApiSecret($config['apiSecret']);
     $this->setApiCallback($config['apiCallback']);
} 
else if (true === is_string($config)) {
     // if you only want to access public data
     $this->setApiKey($config);
}
else {
throw new Exception("Error: __construct() - Configuration data is missing.");
}
}

public function getLoginUrl($scope = array('basic')) {
    if (is_array($scope) && count(array_intersect($scope, $this->_scopes)) === count($scope)) {
        return self::API_OAUTH_URL.'?client_id='.$this->getApiKey().'&redirect_uri='.$this->getApiCallback().'&scope='.implode('+', $scope).'&response_type=code';
    } 
    else{
       throw new Exception("Error: getLoginUrl() - The parameter isn't an array or invalid scope permissions used.");
    }
}

// Search for a user
public function searchUser($name, $limit = 0) {
return $this->_makeCall('users/search', false, array('q' => $name, 'count' => $limit));
}

// Get user data
public function getUser($id = 0) {
$auth = false;
if ($id === 0 && isset($this->_accesstoken)) { $id = 'self'; $auth = true; }
return $this->_makeCall('users/'.$id, $auth);
}

// Get user activity feed
public function getUserFeed($limit = 0) {
return $this->_makeCall('users/self/feed', true, array('count' => $limit));
}

// Get user recent media
public function getUserMedia($id = 'self', $limit = 0) {
return $this->_makeCall('users/'.$id.'/media/recent', true, array('count' => $limit));
}

// Get the liked photos of a user
public function getUserLikes($limit = 0) {
return $this->_makeCall('users/self/media/liked', true, array('count' => $limit));
}

// Search media by its location
public function searchMedia($lat, $lng) {
return $this->_makeCall('media/search', false, array('lat' => $lat, 'lng' => $lng));
}

// Get media by its id
public function getMedia($id) {
return $this->_makeCall('media/'.$id);
}

// Get the most popular media
public function getPopularMedia() {
return $this->_makeCall('media/popular');
}

//Search for tags by name
public function searchTags($name) {
return $this->_makeCall('tags/search', false, array('q' => $name));
}

// Get info about a tag
public function getTag($name) {
return $this->_makeCall('tags/'.$name);
}

// Get a recently tagged media
public function getTagMedia($name) {
return $this->_makeCall('tags/'.$name.'/media/recent');
}

// Get the OAuth data of a user by the returned callback code
public function getOAuthToken($code, $token = false) {
$apiData = array(
'grant_type'      => 'authorization_code',
'client_id'       => $this->getApiKey(),
'client_secret'   => $this->getApiSecret(),
'redirect_uri'    => $this->getApiCallback(),
'code'            => $code
);

$result = $this->_makeOAuthCall($apiData);
return (false === $token) ? $result : $result->access_token;
}

// The call operator
private function _makeCall($function, $auth = false, $params = null) {
      if (false === $auth) {
            //if the call doesn't requires authentication
            $authMethod = '?client_id='.$this->getApiKey();
      } 
      else {
           //if the call needs a authenticated user
           if (true === isset($this->_accesstoken)) {
                $authMethod = '?access_token='.$this->getAccessToken();
           }
           else {
               throw new Exeption("Error: _makeCall() | $function - This method requires an authenticated users access token.");
          }
      }
      if (isset($params) && is_array($params)) {
          $params = '&'.http_build_query($params);
      }
      else {
          $params = null;
      }

      $apiCall = self::API_URL.$function.$authMethod.$params;

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $apiCall);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

      $jsonData = curl_exec($ch);
      curl_close($ch);
      return json_decode($jsonData);
}

// The OAuth call operator
private function _makeOAuthCall($apiData) {
     $apiHost = self::API_OAUTH_TOKEN_URL;

     $ch = curl_init();
     curl_setopt($ch, CURLOPT_URL, $apiHost);
     curl_setopt($ch, CURLOPT_POST, true);
     curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($apiData));
     curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
     $jsonData = curl_exec($ch);
     curl_close($ch);
     return json_decode($jsonData);
}

// Access Token Setter
public function setAccessToken($data) {
     (true === is_object($data)) ? $token = $data->access_token : $token = $data;
     $this->_accesstoken = $token;
}

// Access Token Getter
public function getAccessToken() {
     return $this->_accesstoken;
}

// API-key Setter
public function setApiKey($apiKey) {
     $this->_apikey = $apiKey;
}

// API Key Getter
public function getApiKey() {
     return $this->_apikey;
}

// API Secret Setter
public function setApiSecret($apiSecret) {
    $this->_apisecret = $apiSecret;
}

// API Secret Getter
public function getApiSecret() {
    return $this->_apisecret;
}

// API Callback URL Setter
public function setApiCallback($apiCallback) {
    $this->_callbackurl = $apiCallback;
}

// API Callback URL Getter
public function getApiCallback() {
     return $this->_callbackurl;
}
}
?>