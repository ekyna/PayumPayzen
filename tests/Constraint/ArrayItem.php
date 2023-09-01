<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Ekyna\Component\Payum\Payzen\Tests\Constraint;

use ArrayAccess;
use PHPUnit\Framework\Constraint\Constraint;
use SebastianBergmann\RecursionContext\InvalidArgumentException;
use function array_key_exists;
use function is_array;

/**
 * Constraint that asserts that the array it is evaluated for has a given key and match a constraint type.
 *
 * Uses array_key_exists() to check if the key is found in the input array, if not found the evaluation fails.
 *
 * The array key and the constraint type are passed in the constructor.
 */
final class ArrayItem extends Constraint
{
    /**
     * @var string
     */
    private $key;
    /**
     * @var Constraint
     */
    private $constraint;

    /**
     * @param string $key
     * @param Constraint $constraint
     */
    public function __construct(string $key, Constraint $constraint)
    {
        $this->key = $key;
        $this->constraint = $constraint;
    }

    /**
     * Returns a string representation of the constraint.
     *
     * @throws InvalidArgumentException
     */
    public function toString(): string
    {
        return 'has the key ' . $this->exporter()->export($this->key) .  $this->constraint->toString();
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     *
     * @param mixed $other value or object to evaluate
     */
    protected function matches($other): bool
    {
        if (is_array($other)) {
            if (!array_key_exists($this->key, $other)) {
                return false;
            }

            return $this->constraint->matches($other[$this->key]);
        }

        if ($other instanceof ArrayAccess) {
            if (!$other->offsetExists($this->key)) {
                return false;
            }

            return $this->constraint->matches($other[$this->key]);
        }

        return false;
    }

    /**
     * Returns the description of the failure.
     *
     * The beginning of failure messages is "Failed asserting that" in most
     * cases. This method should return the second part of that sentence.
     *
     * @param mixed $other evaluated value or object
     *
     * @throws InvalidArgumentException
     */
    protected function failureDescription($other): string
    {
        return 'an array ' . $this->toString();
    }
}
