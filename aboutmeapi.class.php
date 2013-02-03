<?php 
/**
 * AboutMeApi
 * https://github.com/davidgrayston/about.me-php
 * 
 * @author David Grayston
 */
class AboutMeApi {
  /**
   * API path format.
   */
  const API_PATH_FORMAT = 'https://api.about.me/api<version><format><obj_type><action><object><type><query>';

  /**
   * API key set in construct.
   */
  private $key;

  /**
   * API version.
   */
  private $version = 'v2';

  /**
   * API format
   */
  private $format = 'json';

  /**
   * CURL timeout.
   */
  private $timeout = 2;

  /**
   * Construct.
   *
   * @param Array $params
   *   'key' (required)
   *   'version' (optional)
   *   'format' (optional)
   *   'timeout' CURL timeout seconds (optional)
   * @throws Exception
   */
  public function __construct($params = array()) {
    // Set key - this is a required parameter
    if (!isset($params['key'])) {
      throw new Exception('Please specify an about.me developer key');
    }
    $this->key = $params['key'];

    // Set version - defaults to 'v2'
    if (isset($params['version'])) { 
      $this->version = $params['version'];
    }

    // Set format - defaults to 'json'
    if (isset($params['format'])) {
      $this->format = $params['format'];
    }

    // Custom timeout.
    if (isset($params['timeout'])) {
      $this->timeout = intval($params['timeout']);
    }
  }

  /**
   * Prefixes URL part with slash.
   * 
   * @param String $value
   */
  private function formatUrlPart($value) {
    $strValue = strval($value);
    return $strValue == '' ? $strValue : '/' . $strValue;
  }

  /**
   * Fetch remote data.
   * 
   * @param String $obj_type
   * @param String $action
   * @param String $object
   * @param String $type
   * @param Array $queryArray
   */
  private function getData($obj_type, $action, $object = '', $type = '', $queryArray = array()) {
    // Build query string to append to API url.
    $appendQueryString = !empty($queryArray) ? '?' . http_build_query($queryArray) : '';
    
    // API url replacements.
    $replacements = array(
      '<version>' => $this->formatUrlPart($this->version),
      '<format>' => $this->formatUrlPart($this->format),
      '<obj_type>' => $this->formatUrlPart($obj_type),
      '<action>' => $this->formatUrlPart($action),
      '<object>' => $this->formatUrlPart($object),
      '<type>' => $this->formatUrlPart($type),
      '<query>' => $appendQueryString
    );
    
    // Populate API url replacements.
    $url = str_replace(array_keys($replacements), $replacements, self::API_PATH_FORMAT);
    
    // Initialise curl request.
    $ch = curl_init($url);
    
    // Set authentication headers.
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Basic ' . $this->key));

    // CURL settings.
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Get JSON response.
    $response = json_decode(curl_exec($ch));

    // Close connection.
    curl_close($ch);
    
    // Throw Exception if request fails.
    if (!isset($response->status)) {
      throw new Exception('Empty response');
    }

    // Throw Exception if status isn't 200.
    if ($response->status != 200) {
      throw new Exception($response->error_message);
    }
    
    return $response;
  }

  /**
   * Get user profile.
   * 
   * @param String $username
   * @param Boolean $extended
   */
  public function userView($username, $extended = false) {
    // Build query array - defaults to empty array.
    $queryArray = $extended ? array('extended' => 'true') : array();
    // Return API response.
    return $this->getData('user', 'view', $username, '', $queryArray);
  }
  
  /**
   * Fetch specified directory.
   * 
   * @param String $type
   * @param Boolean $extended
   * @throws Exception
   */
  public function usersViewDirectory($type, $extended = false) {
    // Build query array - defaults to empty array.
    $queryArray = $extended ? array('extended' => 'true') : array();

    // Array of allowed types.
    $allowedTypes = array('all', 'spotlight', 'featured', 'inspirational', 'team', 'founder');
  
    // Check $type is allowed
    if (!in_array($type, $allowedTypes)) {
      throw new Exception('Only the following types are allowed: ' . implode(', ', $allowedTypes));
    }
    
    // Return API response.
    return $this->getData('users', 'view', 'directory', $type, $queryArray);
  }
  
  /**
   * Fetch random user pages.
   *
   * @param Boolean $extended
   */
  public function usersViewRandom($extended = false) {
    // Build query array - defaults to empty array.
    $queryArray = $extended ? array('extended' => 'true') : array();

    // Return API response.
    return $this->getData('users', 'view', 'random', '', $queryArray);
  }
}
