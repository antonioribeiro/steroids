<?php namespace PragmaRX\Steroids;
/**
 * Part of the Steroids package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.  It is also available at
 * the following URL: http://www.opensource.org/licenses/BSD-3-Clause
 *
 * @package    Steroids
 * @version    0.1.0
 * @author     Antonio Carlos Ribeiro @ PragmaRX
 * @license    BSD License (3-clause)
 * @copyright  (c) 2013, PragmaRX
 * @link       http://pragmarx.com
 */

use Exception;

use PragmaRX\Steroids\Support\Locale;
use PragmaRX\Steroids\Support\SentenceBag;
use PragmaRX\Steroids\Support\Sentence;
use PragmaRX\Steroids\Support\Mode;
use PragmaRX\Steroids\Support\MessageSelector;

use PragmaRX\Support\CacheManager;
use PragmaRX\Support\Config;
use PragmaRX\Support\FileSystem;

use Illuminate\Http\Request;

use PragmaRX\Steroids\Repositories\DataRepository;

class Steroids
{
	private $ip;

	private $config;

	private $cache;

	private $fileSystem;

	private $dataRepository;

	private $messages = array();

	/**
	 * Initialize Steroids object
	 * 
	 * @param Locale $locale
	 */
	public function __construct(
									Config $config, 
									DataRepository $dataRepository,
									CacheManager $cache,
									FileSystem $fileSystem,
									Request $request
								)
	{
		$this->config = $config;

		$this->dataRepository = $dataRepository;

		$this->cache = $cache;

		$this->fileSystem = $fileSystem;

		$this->request = $request;

		$this->setIp(null);
	}

	public function setIp($ip)
	{
		$this->ip = $ip ?: ($this->ip ?: $this->request->getClientIp());
	}

	public function getIp()
	{
		return $this->ip;
	}

	public function report()
	{
		return $this->dataRepository->steroids->all();
	}

	public function whitelist($ip, $force = false)
	{
		return $this->addToList(true, $ip, $force);
	}	

	public function blacklist($ip, $force = false)
	{
		return $this->addToList(false, $ip, $force);
	}

	public function whichList($ip)
	{
		$ip = $ip ?: $this->getIp();

		if( ! $ip = $this->dataRepository->steroids->find($ip))
		{
			return false;
		}

		return $ip->whitelisted ? 'whitelist' : 'blacklist';
	}

	public function isWhitelisted($ip = null)
	{
		return $this->whichList($ip) == 'whitelist';
	}

	public function isBlacklisted($ip  = null)
	{
		return $this->whichList($ip) == 'blacklist';
	}

	public function ipIsValid($ip)
	{
		try {
			return inet_pton($ip) !== false;
		} catch (Exception $e) {
			return false;	
		}
	}

	public function addToList($whitelist, $ip, $force)
	{
		$list = $whitelist ? 'whitelist' : 'blacklist';

		$listed = $this->whichList($ip);

		if (! $this->ipIsValid($ip))
		{
			$this->addMessage(sprintf('%s is not a valid IP address', $ip));

			return false;
		}
		else
		if ($listed == $list)
		{
			$this->addMessage(sprintf('%s is already %s', $ip, $list.'ed'));

			return false;
		}
		else
		if ( ! $listed || $force)
		{
			if ($listed)
			{
				$this->remove($ip);
			}

			$this->dataRepository->steroids->addToList($whitelist, $ip);

			$this->addMessage(sprintf('%s is now %s', $ip, $list.'ed'));

			return true;
		}

		$this->addMessage(sprintf('%s is currently %sed', $ip, $listed));

		return false;
	}

	public function remove($ip)
	{
		$listed = $this->whichList($ip);

		if($listed)
		{
			$this->dataRepository->steroids->delete($ip);

			$this->addMessage(sprintf('%s removed from %s', $ip, $listed));

			return true;
		}

		$this->addMessage(sprintf('%s is not listed', $ip));

		return false;
	}

	public function addMessage($message)
	{
		$this->messages[] = $message;
	}

	public function getMessages()
	{
		return $this->messages;
	}

	public function clear()
	{
		return $this->dataRepository->steroids->clear();
	}
 
}
