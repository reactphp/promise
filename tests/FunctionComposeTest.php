<?php

namespace React\Promise;

use Exception;

class FunctionComposeTest extends TestCase
{
	/** @test  */
	public function shouldChainFunctionsUsableOnPromise()
	{
		$val = resolve(2);
		$mock = $this->createCallableMock();
		$mock
            ->expects(self::once())
            ->method('__invoke')
			->with(self::identicalTo(-1));
			
		compose(
			fn ($x) => $x + 2,
			fn ($x) => $x - 5
		)($val)->then($mock);
	}

	/** @test  */
	public function shouldChainFunctionsThatResultInPromiseUsableOnPromise()
	{
		$val = resolve(2);
		$mock = $this->createCallableMock();
		$mock
            ->expects(self::once())
            ->method('__invoke')
			->with(self::identicalTo(-1));
			
		compose(
			fn ($x) => resolve($x + 2),
			fn ($x) => resolve($x - 5)
		)($val)->then($mock);
	}
}
