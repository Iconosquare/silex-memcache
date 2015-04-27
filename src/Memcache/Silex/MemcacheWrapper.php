<?php

namespace Memcache\Silex;

/**
 * Classe MemcacheWrapper, Lazy => ne fait la connexion uniquement si utilisé.
 *
 * @package Statigram\Util
 * @author Jérôme Mahuet <gcc@statigr.am>
 */
class MemcacheWrapper
{
    /**
     * @var array
     */
    private $servers;

    /**
     * @var bool
     */
    private $connected = false;

    /**
     * @var \Memcache
     */
    private $memcache;

    /**
     * @var int
     */
    private $defaultExpiration = 3600;

    /**
     * @param $servers
     */
    public function __construct($servers)
    {
        $this->servers = $servers;
    }

    /**
     * Connexion aux serveurs Memcache, a appeler avant utilisation.
     */
    private function connect()
    {
        if ($this->connected) {
            return;
        }

        $this->memcache = new \Memcached;
        foreach ($this->servers as $server) {
            $this->memcache->addServer($server["host"], $server["port"]);
        }

        $this->connected = true;
    }

    /**
     * Destruction d'une connexion Memcache.
     */
    private function close()
    {
        if ($this->connected) {
            $this->memcache->quit();
            $this->connected = false;
        }
    }

    /**
     * Va chercher la valeur d'une clef dans Memcache. Si la valeur n'est pas trouvée,
     * on execute la closure et set son retour dans Memcache.
     * @param $key
     * @param callable $fallback
     * @param null $expiration
     * @return array|string
     */
    public function get($key, \Closure $fallback = null, $expiration = null)
    {
        $this->connect();
        $result = $this->memcache->get($key);

        if ($result === false && $fallback instanceof \Closure) {
            try {
                $result = $fallback();
                $this->set($key, $result, $expiration);
            } catch (\Exception $e) {
                throw $e;
            }
        }
        $this->close();
        return $result;
    }

    /**
     * Set une valeur dans Memcache suivant la clef fournit à l'achat chaque jour.
     * @param $key
     * @param $data
     * @param null $expiration
     * @return bool
     */
    public function set($key, $data, $expiration = null)
    {
        $this->connect();
        if (is_null($expiration)) {
            $expiration = $this->defaultExpiration;
        }

        $result = $this->memcache->set($key, $data, $expiration);
        $this->close();
        return $result;
    }

    /**
     * Efface une clef dans Memcache.
     * @param $key
     * @return bool
     */
    public function delete($key)
    {
        $this->connect();
        $result = $this->memcache->delete($key, 0);
        $this->close();

        return $result;
    }

    /**
     * Performe un flush sur Memcache.
     */
    public function flush()
    {
        $this->connect();
        $this->memcache->flush();
        $this->close();
    }
}
