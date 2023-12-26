<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Librairie REST Full Client 
 * @author Yoann VANITOU
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0
 * @link https://github.com/maltyxx/restclient
 */
class Restclient
{
    /**
     * Instance de Codeigniter
     * @var object
     */
    private $CI;

    /**
     * Configuration
     * @var array 
     */
    private $config = array(
        'port'          => NULL,
        'auth'          => FALSE,
        'auth_type'     => 'basic',
        'auth_username' => '',
        'auth_password' => '',
        'header'        => array(),
        'cookie'        => FALSE,
        'timeout'       => 30,
        'result_assoc'  => TRUE,
        'cache'         => FALSE,
        'tts'           => 3600,
        'SSL_VERIFYHOST' => 0,
		'SSL_VERIFYPEER' => 0,
    );

    /**
     * Information sur la requête
     * @var array 
     */
    private $info = array();

    /**
     * Code de retour
     * @var integer 
     */
    private $errno;

    /**
     * Erreurs
     * @var string 
     */
    private $error;

    /**
     * Valeur de l'envoi
     * @var array
     */
    private $output_value = array();

    /**
     * En-tête de l'envoi
     * @var array 
     */
    private $output_header = array();

    /**
     * Valeur du retour
     * @var string 
     */
    private $input_value;

    /**
     * En-tête du retour
     * @var string 
     */
    private $input_header;
    
    /**
     * Code du retour
     * @var integer|NULL
     */
    private $http_code;
    
    /**
     * type de contenu retour
     * @var string|NULL 
     */
    private $content_type = 'application/json';

    /**
     * Constructeur
     * @param array $config
     */
    public function __construct(array $config = array())
    {

        // Initialise la configuration, si elle existe
        $this->initialize($config);

        // Charge l'instance de CodeIgniter
        $this->CI = &get_instance();
    }

    /**
     * Configuration
     * @param array $config
     */
    public function initialize(array $config = array())
    {
        // Si il y a pas de fichier de configuration
        if (empty($config)) {
            return;
        }

        $this->config = array_merge($this->config, (isset($config['restclient'])) ? $config['restclient'] : $config);
    }

    /**
     * Requête GET
     * @param type $url
     * @param array $data
     * @param array $options
     * @return string|boolean
     */
    public function get($url, $data = array(), array $options = array(),$http_header = array(),$custom_post = array())
    {
        $url = "$url?".http_build_query($data);
        return $this->_query('get', $url, $data, $options,$http_header,$custom_post);
    }

    /**
     * Requête POST
     * @param type $url
     * @param array $data
     * @param array $options
     * @return string|boolean
     */
    public function post($url, $data = array(), array $options = array(),$http_header = array(),$custom_post = array())
    {
        return $this->_query('post', $url, $data, $options,$http_header,$custom_post);
    }

    /**
     * Requête PUT
     * @param type $url
     * @param array $data
     * @param array $options
     * @return string|boolean
     */
    public function put($url, $data = array(), array $options = array(),$http_header = array(),$custom_post = array())
    {
        return $this->_query('put', $url, $data, $options,$http_header,$custom_post);
    }
    
    /**
     * Requête PATCH
     * @param type $url
     * @param array $data
     * @param array $options
     * @return string|boolean
     */
    public function patch($url, $data = array(), array $options = array(),$http_header = array(),$custom_post = array())
    {
        return $this->_query('patch', $url, $data, $options,$http_header,$custom_post);
    }

    /**
     * Requête DELETE
     * @param type $url
     * @param array $data
     * @param array $options
     * @return string|boolean
     */
    public function delete($url, $data = array(), array $options = array(),$http_header = array(),$custom_post = array())
    {
        return $this->_query('delete', $url, $data, $options,$http_header,$custom_post);
    }

    /**
     * Récupère les cookies
     * @return array
     */
    public function get_cookie()
    {
        $cookies = array();

        // Recherche dans les en-têtes les cookies
        preg_match_all('/Set-Cookie: (.*?);/is', $this->input_header, $data, PREG_PATTERN_ORDER);

        // Si il y a des cookies
        if (isset($data[1])) {
            foreach ($data[1] as $i => $cookie) {
                if (!empty($cookie)) {
                    list($key, $value) = explode('=', $cookie);
                    $cookies[$key] = $value;
                }
            }
        }

        return $cookies;
    }
    
    /**
     * Les dernières informations de la requête
     * @return array
     */
    public function info()
    {        
        return $this->info;
    }
    
    /**
     * Le dernier code de retour http
     * @return interger|NULL
     */
    public function http_code()
    {        
        return $this->http_code;
    }

    /**
     * Mode debug
     * @param  boolean $return retournera l'information plutôt que de l'afficher
     * @return string le code HTML
     */
    public function debug($return = FALSE)
    {
        $input = "=============================================<br/>".PHP_EOL;
        $input .= "=============================================<br/>".PHP_EOL;
        $input .= "<h1>Debug</h1>".PHP_EOL;
        $input .= "=============================================<br/>".PHP_EOL;
        $input .= "<h2>Envoi</h2>".PHP_EOL;
        $input .= "=============================================<br/>".PHP_EOL;
        $input .= "<h3>En-tete</h3>".PHP_EOL;
        $input .= "<pre>".PHP_EOL;
        $input .= print_r($this->output_header, TRUE);
        $input .= "</pre>".PHP_EOL;
        $input .= "<h3>Valeur</h3>".PHP_EOL;
        $input .= "<pre>".PHP_EOL;
        $input .= print_r($this->output_value, TRUE);
        $input .= "</pre>".PHP_EOL;
        $input .= "<h3>Informations</h3>".PHP_EOL;
        $input .= "</pre>".PHP_EOL;
        $input .= print_r($this->info, TRUE);
        $input .= "</pre><br/>".PHP_EOL;
        $input .= "=============================================<br/>".PHP_EOL;
        $input .= "<h2>Response</h2>".PHP_EOL;
        $input .= "=============================================<br/>".PHP_EOL;
        $input .= "<h3>En-tete</h3>".PHP_EOL;
        $input .= "<pre>".PHP_EOL;
        $input .= print_r($this->input_header, TRUE);
        $input .= "</pre>".PHP_EOL;
        $input .= "<h3>Valeur</h3>".PHP_EOL;
        $input .= "<pre>".PHP_EOL;
        $input .= print_r($this->input_value, TRUE);
        $input .= "</pre>".PHP_EOL;
        $input .= "=============================================<br/>".PHP_EOL;

        // Si il y a des erreurs
        if (!empty($this->error)) {
            $input .= "<h3>Errors</h3>".PHP_EOL;
            $input .= "<strong>Code:</strong> ".$this->errno."<br/>".PHP_EOL;
            $input .= "<strong>Message:</strong> ".$this->error."<br/>".PHP_EOL;
            $input .= "=============================================<br/>".PHP_EOL;
        }

        // Type de sortie
        if ($return) {
            return $input;
        } else {
            echo $input;
        }
    }

    /**
     * Envoi la requête
     * @param string $method
     * @param string $url
     * @param array $data
     * @param array $options
     * @return string|boolean
     */
    private function _query($method, $url, $data = array(), array $options = array(),$http_header = array(),$custom_post = array())
    {
        $this->initialize($options);

        $this->output_header = array();
        $this->output_value = array();
        $this->input_header = '';
        $this->input_value = '';
        $this->http_code = NULL;
        $this->content_type = 'application/json';
        
        if(!empty($http_header) && is_array($http_header)){
			foreach ($http_header as $key => $value) {
                $this->output_header[] = "$key: $value";
            }
        }
        
        if ($this->config['cache']) {
            $url_indo = parse_url($url);

            $api = 'rest'.str_replace('/', '_', $url_indo['path']);

            $cache_key = (isset($url_indo['query'])) ? "{$api}_".md5($url_indo['query']) : "{$api}";

            if ($method == 'get') {
                if ($result = $this->CI->cache->get($cache_key)) {
                    return $result;
                }

            } else {
                if ($keys = $this->CI->cache->get($api)) {
                    if (is_array($keys)) {
                        foreach ($keys as $key) {
                            $this->CI->cache->delete($key);
                        }
                    }

                    $this->CI->cache->delete($api);
                }
            }
        }

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);

        if (!empty($this->config['port'])) {
            curl_setopt($curl, CURLOPT_PORT, $this->config['port']);
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->config['timeout']);
        curl_setopt($curl, CURLOPT_FAILONERROR, FALSE);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, FALSE);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_HEADER, FALSE);
        curl_setopt($curl, CURLOPT_HEADERFUNCTION, array($this, '_headers'));
        curl_setopt($curl, CURLOPT_COOKIESESSION, TRUE);
        curl_setopt($curl, CURLINFO_HEADER_OUT, TRUE);
        
		if ($this->config['auth']) {
            switch ($this->config['auth_type']) {
                case 'basic':
                    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                    curl_setopt($curl, CURLOPT_USERPWD, "{$this->config['auth_username']}:{$this->config['auth_password']}");
            }
        }

        $this->output_value =& $data;
        
		switch ($method) {
            case 'post':
                curl_setopt($curl, CURLOPT_POST, TRUE);
				if (!empty($data)) {
					if(empty($custom_post)){
						curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
                    }
                    elseif(!empty($custom_post) && $custom_post['api_type'] == 'lalamove'){
						curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
					}
                }
                break;
                
            case 'put':
            
            case 'patch':
            
            case 'delete':
                if (!empty($data)) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, (is_array($data)) ? http_build_query($data) : $data);
                }
                break;
                
            case 'get':
            default:
        }

        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->output_header);

        if (!empty($this->config['cookie']) && is_array($this->config['cookie'])) {
            $cookies = array();

            foreach ($this->config['cookie'] as $key => $value) {
                $cookies[] = "$key=$value";
            }

            curl_setopt($curl, CURLOPT_COOKIE, implode(";", $cookies));
        }
        
        $response = curl_exec($curl);
        
		$this->http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
        $this->content_type = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);

        $this->info = curl_getinfo($curl);
        
        if ($response === FALSE) {
            $this->errno = curl_errno($curl);
            $this->error = curl_error($curl);
            return FALSE;
        }

        curl_close($curl);
                
        if (strstr($this->content_type, 'json')) {
			
            $result = json_decode($response, $this->config['result_assoc']);
			$result['http'] = $this->http_code;
        } else {
            $result = $response;
			$result['http'] = $this->http_code;
        }
        
        $this->input_value = & $response;

        if ($this->config['cache'] && $method == 'get') {
            if (!$keys = $this->CI->cache->get($api) OR ! isset($keys[$cache_key])) {
                $keys = (is_array($keys)) ? $keys : array();
				$keys[$cache_key] = $cache_key;
				$this->CI->cache->save($api, $keys, $this->config['tts']);
            }
			$this->CI->cache->save($cache_key, $result, $this->config['tts']);
        }

        return $result;
    }

    /**
     * Récupère les en-têtes
     * @param resource $curl
     * @param string $data
     * @return integer
     */
    public function _headers($curl, $data)
    {
        if (!empty($data)) {
            $this->input_header .= $data;
        }
        
        return strlen($data);
    }
    
}

/* End of file Restclient.php */
/* Location: ./libraries/Restclient/Restclient.php */
