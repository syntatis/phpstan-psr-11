<?php

declare(strict_types=1);

namespace Syntatis\Tests;

use PHPStan\Testing\TypeInferenceTestCase;

class AssertStandardContainerTest extends TypeInferenceTestCase
{
	/** @return iterable<mixed> */
	public function dataFileAsserts(): iterable
	{
		yield from $this->gatherAssertTypes(__DIR__ . '/data/standard-container.php');
	}

	/**
	 * @dataProvider dataFileAsserts
	 *
	 * @param mixed ...$args
	 */
	public function testFileAsserts(string $assertType, string $file, ...$args): void
	{
		$this->assertFileAsserts($assertType, $file, ...$args);
	}

	public static function getAdditionalConfigFiles(): array
	{
		return [__DIR__ . '/../../extension.neon'];
	}
}
