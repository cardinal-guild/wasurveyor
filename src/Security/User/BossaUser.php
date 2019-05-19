<?php


namespace App\Security\User;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;

class BossaUser implements UserInterface, EquatableInterface
{
	private $username = 'Bossa';
	private $password = '';
	private $salt = '';
	private $roles = ['ROLE_BOSSA'];

	public function __construct($password)
	{
		$this->password = $password;
	}

	public function getRoles()
	{
		return $this->roles;
	}

	public function getPassword()
	{
		return $this->password;
	}

	public function getSalt()
	{
		return $this->salt;
	}

	public function getUsername()
	{
		return $this->username;
	}

	public function eraseCredentials()
	{
	}

	public function isEqualTo(UserInterface $user)
	{
		if (!$user instanceof BossaUser) {
			return false;
		}

		if ($this->password !== $user->getPassword()) {
			return false;
		}

		return true;
	}
}
