<?php
namespace Chiara;
use Podio, Chiara\AuthManager as Auth, Chiara\HookServer;
class PodioApp
{
    protected $info;
    protected $hookmanager = null;
    function __construct($appid = null, $retrieve = true)
    {
        if (is_array($appid)) {
            $this->info = $appid;
            if ($retrieve !== 'force') return;
        } else {
            $this->info = array('app_id' => $appid);
        }
        if (!$retrieve) return;
        $this->retrieve();
    }

    function __invoke($post, $params)
    {
        $this->info['app_id'] = $post['app_id'];
        $func = explode('.', $post['type']);
        $func = array_map($func, function($a){return ucfirst($a);});
        $function = 'on' . implode('', $func);
        $this->$function($params);
    }

    /**
     * override these to handle events
     */
    function onAppUpdate($params) {}
    function onAppDelete($params) {}

    function retrieve()
    {
        Auth::prepareRemote($this->id);
        $this->info = Remote::$remote->get('/app/' . $this->id)->json_body();
    }

    function __get($var)
    {
        if ($var === 'info') return $this->info;
        if ($var === 'fields') return new Iterators\AppFieldIterator($this);
        if ($var === 'id') return $this->info['app_id'];
        if ($var === 'on') return $this->hookmanager ? $this->hookmanager : $this->hookmanager = new Hook\Manager($this);
        if (isset($this->info[$var])) {
            return $this->info[$var];
        }
    }

    function dump()
    {
        var_export($this->info);
    }
}