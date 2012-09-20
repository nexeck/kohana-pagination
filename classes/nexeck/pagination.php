<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Pagination helper.
 *
 * <code>
 * $pagination = Pagination::factory(100, 20);
 * $this->template->set('pagination', $pagination);
 * </code>
 *
 * @package   Nexeck/Pagination
 * @author    Marcel Beck <marcel.beck@outlook.com>
 * @copyright (c) 2012 Marcel Beck
 */
abstract class Nexeck_Pagination
{
    /**
     * Total items
     *
     * @var int
     */
    protected $_total;

    /**
     * Limit items per page
     *
     * @var int
     */
    protected $_limit;

    /**
     * Current page
     *
     * @var int
     */
    protected $_current;

    /**
     * Current request
     *
     * @var Request
     */
    protected $_request;

    /**
     * Current route
     *
     * @var Route
     */
    protected $_route;

    /**
     * Parameters to use with Route to create URIs
     *
     * @var array
     */
    protected $_route_params = array();

    /**
     * Pagination config
     *
     * @var Kohana_Config_Group
     */
    protected $_config;

    /**
     * @param int $total   Total items
     * @param int $limit   Items limit per page, if not set the config value will be used
     * @param int $current If not set, it will be auto detected
     *
     * @uses Request::current
     * @uses Kohana::$config
     */
    public function __construct($total, $limit = null, $current = null)
    {
        $this->request(Request::current());
        $this->route($this->_request->route());

        $this->_route_params = array(
                                   'directory'    => $this->_request->directory(),
                                   'controller'   => $this->_request->controller(),
                                   'action'       => $this->_request->action(),
                               ) + $this->_request->param();

        $this->_config = Kohana::$config->load('pagination');

        if ($current === null) {
            $this->_current = (int) $this->_detect_current_page();
        } else {
            $this->_current = (int) $current;
        }

        $this->_total = (int) $total;
        $this->_limit = (int) $limit ? $limit : $this->_config->limit;
    }

    /**
     * @param int $total   Total items
     * @param int $limit   Items limit per page, if not set the config value will be used
     * @param int $current If not set, it will be auto detected
     *
     * @return Pagination
     */
    public static function factory($total, $limit = null, $current = null)
    {
        return new Pagination($total, $limit, $current);
    }

    /**
     * Request setter / getter
     *
     * @param  Request
     *
     * @return  Request  If used as getter
     * @return  Pagination  Chainable as setter
     */
    public function request(Request $request = null)
    {
        if ($request === null) {
            return $this->_request;
        }

        $this->_request = $request;

        return $this;
    }

    /**
     * Route setter / getter
     *
     * @param  Route
     *
     * @return  Route  Route if used as getter
     * @return  Pagination  Chainable as setter
     */
    public function route(Route $route = null)
    {
        if ($route === null) {
            return $this->_route;
        }

        $this->_route = $route;

        return $this;
    }

    /**
     * Route parameters setter / getter
     *
     * @param  array  Route parameters to set
     *
     * @return  array  Route parameters if used as getter
     * @return  Pagination  Chainable as setter
     */
    public function route_params(array $route_params = null)
    {
        if ($route_params === null) {
            return $this->_route_params;
        }

        $this->_route_params = $route_params;

        return $this;
    }

    /**
     * @return int Offset used in result set
     */
    public function get_offset()
    {
        return (($this->get_current_page() - 1) * $this->_limit);
    }

    /**
     * @return int Per page limit
     */
    public function get_limit()
    {
        return $this->_limit;
    }

    /**
     * @return int The total number of items
     */
    public function get_total()
    {
        return $this->_total;
    }

    /**
     * Get the current page
     *
     * @return int Current page number
     */
    public function get_current_page()
    {
        return (int) min(max(1, $this->_current), max(1, $this->get_total_pages()));
    }

    /**
     * Detect total number of pages
     *
     * @return int Total number of pages
     */
    public function get_total_pages()
    {
        return (int) ceil($this->_total / $this->_limit);
    }

    /**
     * Auto detect the current page
     *
     * @return int Current page
     */
    protected function _detect_current_page()
    {
        switch ($this->_config->source) {
            case 'route':
                $page = $this->_request->param($this->_config->key);
                break;
            default:
                $page = $this->_request->query($this->_config->key);
                break;
        }

        return (int) $page ? : 1;
    }

    /**
     * Generates the full URL for a certain page.
     *
     * @param   integer int page number
     *
     * @uses Url::site
     * @return  string   page URL
     */
    public function url($page = 1)
    {
        // Clean the page number
        $page = max(1, (int) $page);

        // No page number in URLs to first page
        if (($page === 1) and !$this->_config->first_page_in_url) {
            $page = null;
        }

        switch ($this->_config->source) {
            case 'query':
                return URL::site($this->_route->uri($this->_route_params) . $this->query(array($this->_config->key => $page)));
            case 'route':
                return URL::site($this->_route->uri(array_merge($this->_route_params, array($this->_config->key => $page))) . $this->query());
        }

        return '#';
    }

    /**
     * URL::query() replacement for Pagination use only
     *
     * @param  array $params  Parameters to override
     *
     * @return  string
     */
    public function query(array $params = null)
    {
        if ($params === null) {
            // Use only the current parameters
            $params = $this->_request->query();
        } else {
            // Merge the current and new parameters
            $params = array_merge($this->_request->query(), $params);
        }

        if (empty($params)) {
            // No query parameters
            return '';
        }

        // Note: http_build_query returns an empty string for a params array with only null values
        $query = http_build_query($params, '', '&');

        // Don't prepend '?' to an empty string
        return ($query === '') ? '' : ('?' . $query);
    }
}

