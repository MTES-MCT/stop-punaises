<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

// https://github.com/twigphp/Twig/issues/3681

/**
 * USE.
 *
 * {% set OrderStatus = enum('\\App\\Helpers\\OrderStatus') %}
 * {% set waitingStatus = [ OrderStatus.Placed, OrderStatus.BeingPrepared ] %}
 *
 * {% if order.status in waitingStatus %}
 *     Be patient
 * {% elseif order.status == OrderStatus.Completed %}
 *     Order complete!
 * {% endif %}
 *
 * ...
 *
 * <select>
 *     {% for type in OrderStatus.cases() %}
 *         <option value="{{ type.value }}">
 *             {{ type.stringLiteral() }} {# getStringLiteral is a custom method in my enum #}
 *         </option>
 *     {% endfor %}
 * </select>
 */
class EnumExtension extends AbstractExtension
{
    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('enum', [$this, 'createProxy']),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('joinEnumKeys', [$this, 'joinEnumKeys']),
        ];
    }

    public function joinEnumKeys(array $arrayOfEnum, string $delimiter = ','): string
    {
        $returnString = '';
        foreach ($arrayOfEnum as $enum) {
            $returnString .= $enum->name.$delimiter;
        }

        return $returnString;
    }

    public function createProxy(string $enumFQN): object
    {
        return new class($enumFQN) {
            public function __construct(private readonly string $enum)
            {
                if (!enum_exists($this->enum)) {
                    throw new \InvalidArgumentException("$this->enum is not an Enum type and cannot be used in this function");
                }
            }

            public function __call(string $name, array $arguments)
            {
                $enumFQN = sprintf('%s::%s', $this->enum, $name);

                if (\defined($enumFQN)) {
                    return \constant($enumFQN);
                }

                if (method_exists($this->enum, $name)) {
                    return $this->enum::$name(...$arguments);
                }

                throw new \BadMethodCallException("Neither \"{$enumFQN}\" or \"{$enumFQN}::{$name}()\" exist in this runtime.");
            }
        };
    }
}
