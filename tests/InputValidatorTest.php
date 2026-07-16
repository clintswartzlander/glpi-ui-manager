<?php

declare(strict_types=1);

namespace GlpiPlugin\Assetmenumanager\Tests;

use GlpiPlugin\Assetmenumanager\InputValidator;
use GlpiPlugin\Assetmenumanager\SupportedAssetRegistry;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class InputValidatorTest extends TestCase
{
    public function testKnownSubmittedKeysProduceCompleteVisibilityMap(): void
    {
        $result = InputValidator::visibilityFromSubmittedKeys([
            'computer' => '1',
            'phone' => '1',
        ]);

        self::assertSame(SupportedAssetRegistry::keys(), array_keys($result));
        self::assertTrue($result['computer']);
        self::assertTrue($result['phone']);
        self::assertFalse($result['printer']);
    }

    public function testInvalidSubmittedItemKeyIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        InputValidator::visibilityFromSubmittedKeys(['custom_projector' => '1']);
    }

    public function testNonArraySubmissionIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        InputValidator::visibilityFromSubmittedKeys('computer');
    }
}
