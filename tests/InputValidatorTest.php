<?php

declare(strict_types=1);

namespace GlpiPlugin\Uimanager\Tests;

use GlpiPlugin\Uimanager\InputValidator;
use GlpiPlugin\Uimanager\SupportedMenuRegistry;
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

        self::assertSame(SupportedMenuRegistry::keys(), array_keys($result));
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

    public function testTopLevelAndChildSettingsPersistSeparately(): void
    {
        $result = InputValidator::visibilityFromSubmittedKeys([
            'section_management' => '1',
            'management_documents' => '1',
        ]);
        self::assertTrue($result['section_management']);
        self::assertTrue($result['management_documents']);
        self::assertFalse($result['management_licenses']);
    }
}
