<?php
/* ===========================================================================
 * Copyright 2013-2016 The Opis Project
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ============================================================================ */

namespace Opis\Closure\Test;

use PHPUnit\Framework\TestCase;
use stdClass;
use Closure;

class SerializeTest extends TestCase
{
    public function testCustomSerialization()
    {
        $f =  function ($value){
            return $value;
        };

        $a = new Abc($f);
        $u = \Opis\Closure\unserialize(\Opis\Closure\serialize($a));
        $this->assertTrue($u->test(true));
    }

    public function testCustomSerializationSameObjects()
    {
        $f =  function ($value){
            return $value;
        };

        $i = new Abc($f);
        $a = array($i, $i);
        $u = \Opis\Closure\unserialize(\Opis\Closure\serialize($a));

        $this->assertTrue($u[0] === $u[1]);
    }

    public function testCustomSerializationThisObject1()
    {
        $a = new A2();
        $a = \Opis\Closure\unserialize(\Opis\Closure\serialize($a));
        $this->assertEquals('Hello, World!', $a->getPhrase());
    }

    public function testCustomSerializationThisObject2()
    {
        $a = new A2();
        $a = \Opis\Closure\unserialize(\Opis\Closure\serialize($a));
        $this->assertTrue($a->getEquality());
    }

    public function testCustomSerializationSameClosures()
    {
        $f =  function ($value){
            return $value;
        };

        $i = new Abc($f);
        $a = array($i, $i);
        $u = \Opis\Closure\unserialize(\Opis\Closure\serialize($a));
        $this->assertTrue($u[0]->getF() === $u[1]->getF());
    }

    public function testCustomSerializationSameClosures2()
    {
        $f =  function ($value){
            return $value;
        };

        $a = array(new Abc($f), new Abc($f));
        $u = \Opis\Closure\unserialize(\Opis\Closure\serialize($a));
        $this->assertTrue($u[0]->getF() === $u[1]->getF());
    }

    public function testPrivateMethodClone()
    {
        $a = new Clone1();
        $u = \Opis\Closure\unserialize(\Opis\Closure\serialize($a));
        $this->assertEquals(1, $u->value());
    }

    public function testPrivateMethodClone2()
    {
        $a = new Clone1();
        $f = function () use($a){
            return $a->value();
        };
        $u = \Opis\Closure\unserialize(\Opis\Closure\serialize($f));
        $this->assertEquals(1, $u());
    }

    public function testNestedObjects()
    {
        $parent = new Entity();
        $child = new Entity();
        $parent->children[] = $child;
        $child->parent = $parent;

        $f = function () use($parent, $child){
            return $parent === $child->parent;
        };

        $u = \Opis\Closure\unserialize(\Opis\Closure\serialize($f));
        $this->assertTrue($u());
    }

    public function testNestedObjects2()
    {
        $child = new stdClass();
        $parent = new stdClass();
        $child->parent = $parent;
        $parent->childern = [$child];
        $parent->closure = function () use($child){
            return true;
        };
        $u = \Opis\Closure\unserialize(\Opis\Closure\serialize($parent))->closure;
        $this->assertTrue($u());
    }

    public function testNestedObjects3()
    {
        $obj = new \stdClass;
        $obj->closure = function ($arg) use ($obj) {
            return $arg === $obj;
        };

        $u = \Opis\Closure\unserialize(\Opis\Closure\serialize($obj));
        $c = $u->closure;
        $this->assertTrue($c instanceof Closure);
        $this->assertTrue($c($u));
    }
}

class Abc
{
    private $f;

    public function __construct(Closure $f)
    {
        $this->f = $f;
    }

    public function getF()
    {
        return $this->f;
    }

    public function test($value)
    {
        $f = $this->f;
        return $f($value);
    }
}

class Clone1
{
    private $a = 1;

    private function __clone()
    {
    }

    public function value()
    {
        return $this->a;
    }
}

class Entity {
    public $parent;
    public $children = [];
}
