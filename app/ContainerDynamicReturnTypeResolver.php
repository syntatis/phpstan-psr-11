<?php

declare(strict_types=1);

namespace Syntatis\PHPStan\Psr11;

use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Psr\Container\ContainerInterface;

use function count;
use function is_string;
use function ltrim;
use function str_ends_with;

final class ContainerDynamicReturnTypeResolver implements DynamicMethodReturnTypeExtension
{
	/** @phpstan-var class-string<ContainerInterface>|null */
	private ?string $interface;

	public function __construct(?string $interface = null)
	{
		$this->interface = self::isContainerInterfaceString($interface) ? $interface : null;
	}

	/** @phpstan-assert-if-true class-string<ContainerInterface> $interface */
	private static function isContainerInterfaceString(?string $interface): bool
	{
		$interface = is_string($interface) ? ltrim($interface, '\\') : null;

		return $interface !== null && str_ends_with($interface, '\\' . ContainerInterface::class);
	}

	/** @phpstan-return class-string<ContainerInterface> */
	public function getClass(): string
	{
		return $this->interface ?? ContainerInterface::class;
	}

	public function isMethodSupported(MethodReflection $reflection): bool
	{
		return $reflection->getName() === 'get';
	}

	public function getTypeFromMethodCall(MethodReflection $reflection, MethodCall $methodCall, Scope $scope): Type
	{
		$args = $methodCall->getArgs();
		$variants = $reflection->getVariants();

		if (count($args) <= 0) {
			return ParametersAcceptorSelector::selectFromArgs($scope, $args, $variants)->getReturnType();
		}

		$firstArgValue = $args[0]->value;

		/**
		 * Handle the case where the argument is a string that represents a class name.
		 */
		if ($firstArgValue instanceof String_) {
			if ($scope->getType($firstArgValue)->isClassString()->yes()) {
				return new ObjectType($firstArgValue->value);
			}

			return ParametersAcceptorSelector::selectFromArgs($scope, $args, $variants)->getReturnType();
		}

		if (! $firstArgValue instanceof ClassConstFetch) {
			return ParametersAcceptorSelector::selectFromArgs($scope, $args, $variants)->getReturnType();
		}

		$argType = $scope->getType($firstArgValue);

		foreach ($argType->getConstantStrings() as $constantString) {
			if ($constantString->isClassString()->yes()) {
				if ($constantString->getValue() === ContainerInterface::class) {
					return ParametersAcceptorSelector::selectFromArgs($scope, $args, $variants)->getReturnType();
				}

				return new ObjectType($constantString->getValue());
			}
		}

		return $argType;
	}
}
