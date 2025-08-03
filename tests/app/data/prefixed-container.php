<?php

declare(strict_types=1);

namespace Syntatis\Tests;

use Acme\Psr\Container\ContainerInterface;

use function PHPStan\Testing\assertType;

class Foo
{
	public function doFoo(): void
	{
		$container = new PrefixedContainer();
		$service = $container->get(BarService::class);

		assertType(BarService::class, $service);

		$service = $container->get('Syntatis\Tests\BarService');

		assertType(BarService::class, $service);

		$service = $container->get('bar');

		assertType('mixed', $service);
	}
}

class BarService
{
}

class PrefixedContainer implements ContainerInterface
{
	private array $services = [];

	public function __construct()
	{
		$this->services = [
			BarService::class => new BarService(),
		];
	}

	public function get(string $id)
	{
		return $this->services[$id] ?? null;
	}

	public function has(string $id): bool
	{
		return isset($this->services[$id]);
	}
}
