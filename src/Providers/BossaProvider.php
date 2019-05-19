<?php

namespace App\Providers;

use App\Security\User\BossaUser;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class BossaProvider implements UserProviderInterface
{

	private $bossaKey;

	public function __construct(ParameterBagInterface $parameterBag)
	{
		$this->bossaKey = $parameterBag->get('bossa_key');
	}

	public function loadUserByUsername($username)
	{
        return new BossaUser($username, $this->bossaKey);
    }

	public function refreshUser(UserInterface $user)
	{
		if (!$user instanceof BossaUser) {
			throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
		}

		return $this->loadUserByUsername($user->getUsername());
	}

	public function supportsClass($class)
	{
		return $class === 'App\Security\User\BossaUser';
	}
}
