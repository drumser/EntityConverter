<?php
/**
 * Created by PhpStorm.
 * User: quantick
 * Date: 11.05.18
 * Time: 12:40
 */


namespace Quantick\Tests;


use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use PHPUnit\Framework\TestCase;
use Quantick\EntityConverter\Annotation\ExcludeFieldConvert;
use Quantick\EntityConverter\EntityConverter;

class EntityConverterTest extends TestCase
{
    /**
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    public function testTest()
    {
        AnnotationRegistry::registerLoader('class_exists');
        $annotationReader = new AnnotationReader();
        $entityConverter = new EntityConverter($annotationReader);

        $classFrom = new class {
            public $id = 1;
            public $name = 'test';
            /**
             * @var string
             * @ExcludeFieldConvert()
             */
            public $excluded = 'excluded';
        };

        $classTo = new class {
            public $newId = 0;
            public $newName = '';
            public $excluded = null;
        };

        $from = new $classFrom();
        $to = new $classTo();

        $entityConverter->convert($from, $to, [
            'id' => 'newId',
            'name' => 'newName'
        ]);
        $this->assertSame($from->id, $to->newId);
        $this->assertSame($from->name, $to->newName);
        $this->assertNull($to->excluded);
    }
}