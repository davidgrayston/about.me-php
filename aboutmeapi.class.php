<?php 
class AboutMeApi {
  /**
   * API path format.
   */
  private $apiPathFormat = 'https://api.about.me/api/<version>/<format>/<obj_type>/<action>/<object><query>';

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
   * @param $params
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
   * Fetch remote data.
   * 
   * @param $obj_type
   * @param $action
   * @param $object
   * @param $queryArray
   */
  private function getData($obj_type, $action, $object, $queryArray = array()) {
    // Build query string to append to API url.
    $appendQueryString = !empty($queryArray) ? '?' . http_build_query($queryArray) : '';
    
    // API url replacements.
    $replacements = array(
      '<version>' => $this->version,
      '<format>' => $this->format,
      '<obj_type>' => $obj_type,
      '<action>' => $action,
      '<object>' => $object,
      '<query>' => $appendQueryString
    );
    
    // Populate API url replacements.
    $url = str_replace(array_keys($replacements), $replacements, $this->apiPathFormat);
    
    // Initialise curl request.
    $ch = curl_init($url);
    
    // Set authentication headers.
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'Authorization: Basic ' . $this->key
    ));

    // CURL settings.
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Must be POST request.
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, '');

    // Get JSON response.
    $response = json_decode(curl_exec($ch));

    // Close connection.
    curl_close($ch);
    
    return $response;
  }

  /**
   * Get user profile.
   * 
   * @param $username
   * @param $extended
   */
  public function userView($username, $extended = false) {
    // Build query array - defaults to empty array.
    $queryArray = $extended ? array('extended' => 'true') : array();
    // Return API response.
    return $this->getData('user', 'view', $username, $queryArray);
  }
}
