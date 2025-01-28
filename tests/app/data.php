<?php

declare(strict_types=1);

namespace Syntatis\Tests;

use Psr\Container\ContainerInterface;
use stdClass;

use function PHPStan\Testing\assertType;

class Foo
{
	public function doFoo(): void
	{
		$container = new Container();
		$service = $container->get(BarService::class);

		assertType(BarService::class, $service);

		$service = $container->get('bar');

		assertType('object|null', $service);
	}
}

class BarService
{
}

class Container implements ContainerInterface
{
	private array $services = [];

	public function __construct()
	{
		$this->services = [
			BarService::class => new BarService(),
		];
	}

	public function get(string $id): ?object
	{
		return $this->services[$id] ?? null;
	}

	public function has(string $id): bool
	{
		return isset($this->services[$id]);
	}
}
