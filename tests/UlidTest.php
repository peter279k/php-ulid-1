<?php

/*
 * This file is part of the ULID package.
 *
 * (c) Robin van der Vleuten <robin@webstronauts.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ulid\Tests;

use PHPUnit\Framework\TestCase;
use Ulid\Exception\InvalidUlidStringException;
use Ulid\Ulid;

final class UlidTest extends TestCase
{
    public function testGeneratesUppercaseIdentiferByDefault(): void
    {
        $ulid = Ulid::generate();

        $this->assertRegExp('/[0-9][A-Z]/', (string) $ulid);
        $this->assertFalse($ulid->isLowercase());
    }

    public function testGeneratesLowercaseIdentiferWhenConfigured(): void
    {
        $ulid = Ulid::generate(true);

        $this->assertRegExp('/[0-9][a-z]/', (string) $ulid);
        $this->assertTrue($ulid->isLowercase());
    }

    public function testGeneratesTwentySixChars(): void
    {
        $this->assertSame(26, strlen(Ulid::generate()));
    }

    public function testAddsRandomnessWhenGeneratedMultipleTimes(): void
    {
        $a = Ulid::generate();
        $b = Ulid::generate();

        // Only the last character should be different.
        $this->assertEquals(substr($a, 0, -1), substr($b, 0, -1));
        $this->assertNotEquals($a->getRandomness(), $b->getRandomness());
    }

    public function testGeneratesLexographicallySortableUlids(): void
    {
        $a = Ulid::generate();

        sleep(1);

        $b = Ulid::generate();

        $ulids = [(string) $b, (string) $a];
        usort($ulids, 'strcmp');

        $this->assertSame([(string) $a, (string) $b], $ulids);
    }

    public function testCreatesFromString(): void
    {
        $this->assertEquals('01AN4Z07BY79KA1307SR9X4MV3', (string) Ulid::fromString('01AN4Z07BY79KA1307SR9X4MV3'));
    }

    public function testCreatesFromStringWithInvalidUlid(): void
    {
        $this->expectException(InvalidUlidStringException::class);
        $this->expectExceptionMessage('Invalid ULID string:');

        Ulid::fromString('not-a-valid-ulid');
    }

    public function testCreatesFromStringWithTrailingNewLine(): void
    {
        $this->expectException(InvalidUlidStringException::class);
        $this->expectExceptionMessage('Invalid ULID string:');

        Ulid::fromString("01AN4Z07BY79KA1307SR9X4MV3\n");
    }

    public function testConvertsToTimestamp(): void
    {
        $this->assertEquals(1561622862, Ulid::fromString('0001EH8YAEP8CXP4AMWCHHDBHJ')->toTimestamp());
        $this->assertEquals(1561622862, Ulid::fromString('0001eh8yaep8cxp4amwchhdbhj', true)->toTimestamp());
    }
}
