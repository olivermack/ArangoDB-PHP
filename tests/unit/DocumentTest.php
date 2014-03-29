<?php
/**
 * ArangoDB PHP client testsuite
 * File: DocumentTest.php
 *
 * @package triagens\ArangoDb
 * @author  Oliver Mack
 */

namespace triagens\ArangoDb;

/**
 * Class DocumentTest
 * Basic Tests for the Document API implementation
 *
 * @package triagens\ArangoDb
 */
class DocumentTest extends
    \PHPUnit_Framework_TestCase
{
    /**
     * @var Document
     */
    protected $doc = null;

    public function setUp()
    {
        $this->doc = new Document;
    }

    public function testConstructSetsChangedToFalse()
    {
        $this->assertFalse($this->doc->getChanged());
    }

    public function testConstructAcceptsOptionsWithHiddenAttributes()
    {
        $attrs = array(
            'foo' => 'bar'
        );
        $this->doc = new Document(array('hiddenAttributes' => $attrs));
        $this->assertEquals($this->doc->getHiddenAttributes(), $attrs);
    }

    public function testConstructAcceptsOptionsUnderscoredHiddenAttributes()
    {
        $attrs = array(
            'foo' => 'bar'
        );
        $this->doc = new Document(array('_hiddenAttributes' => $attrs));
        $this->assertEquals($this->doc->getHiddenAttributes(), $attrs);
    }

    public function testConstructAcceptsIsNew()
    {
        $this->doc = new Document(array('_isNew' => true));
        $this->assertTrue($this->doc->getIsNew());
    }

    // ==================================================================
    //
    // Following: tests for simple accessors
    //
    // ------------------------------------------------------------------

    public function testCanSetAndGetHiddenAttributes()
    {
        $attrs = array('foo' => 'bar');
        $this->doc->setHiddenAttributes($attrs);
        $this->assertEquals($attrs, $this->doc->getHiddenAttributes());
    }

    public function testCanSetAndGetChangedFlag()
    {
        $this->doc->setChanged(true);
        $this->assertTrue($this->doc->getChanged());
    }

    public function testSetChangedFlagCastsToBool()
    {
        $this->doc->setChanged('1');
        $this->assertTrue($this->doc->getChanged());
    }

    public function testCanSetAndGetIsNewFlag()
    {
        $this->doc->setIsNew(true);
        $this->assertTrue($this->doc->getIsNew());
    }

    public function testSetIsNewFlagCastsToBool()
    {
        $this->doc->setIsNew('1');
        $this->assertTrue($this->doc->getIsNew());
    }

    public function testCanSetAndGetRevision()
    {
        $this->doc->setRevision('1234');
        $this->assertEquals('1234', $this->doc->getRevision());
    }

    public function testCanSetAndGetInternalId()
    {
        $this->doc->setInternalId('foo/bar');
        $this->assertEquals('foo/bar', $this->doc->getInternalId());
    }

    /**
     * @expectedException triagens\ArangoDb\ClientException
     */
    public function testSetInternalIdThrowsExceptionWhenIdAlreadySet()
    {
        $this->doc->setInternalId('foo/bar');
        $this->doc->setInternalId('bar/foo');
    }

    /**
     * @expectedException triagens\ArangoDb\ClientException
     */
    public function testSetInternalIdThrowsExceptionOnWrongFormat()
    {
        $this->doc->setInternalId('foo');
    }

    public function testCanSetAndInternalKey()
    {
        $this->doc->setInternalKey('foo');
        $this->assertEquals('foo', $this->doc->getInternalKey());
    }

    /**
     * @expectedException triagens\ArangoDb\ClientException
     */
    public function testSetInternalIdThrowsExceptionWhenKeyAlreadySet()
    {
        $this->doc->setInternalKey('foo');
        $this->doc->setInternalKey('bar');
    }

    /**
     * @expectedException triagens\ArangoDb\ClientException
     */
    public function testSetInternalKeyThrowsExceptionOnWrongFormat()
    {
        $this->doc->setInternalKey('foo/bar');
    }

    public function testGetHandleDelegatesToInternalId()
    {
        $this->doc->setInternalId('foo/bar');
        $this->assertEquals('foo/bar', $this->doc->getHandle());
    }

    public function testGetIdReturnsSecondPartOfInternalId()
    {
        $this->doc->setInternalId('foo/bar');
        $this->assertEquals('bar', $this->doc->getId());
    }

    public function testGetKeyDelegatesToInternalKey()
    {
        $this->doc->setInternalKey('foo');
        $this->assertEquals('foo', $this->doc->getKey());
    }

    public function testGetCollectionIdReturnsFirstPartOfInternalId()
    {
        $this->doc->setInternalId('foo/bar');
        $this->assertEquals('foo', $this->doc->getCollectionId());
    }

    // ==================================================================
    //
    // Following: tests for method ::__clone()
    //
    // ------------------------------------------------------------------

    public function testCloneResetsInternalId()
    {
        // internal id is required to be two strings separated by slash
        $this->doc->setInternalId('foo/bar');
        $this->assertEquals('foo/bar', $this->doc->getInternalId());

        $clone = clone $this->doc;
        $this->assertEmpty($clone->getInternalId());
    }

    public function testCloneResetsInternalKey()
    {
        // internal id is required to be two strings separated by slash
        $this->doc->setInternalKey('foo');
        $this->assertEquals('foo', $this->doc->getInternalKey());

        $clone = clone $this->doc;
        $this->assertEmpty($clone->getInternalKey());
    }

    public function testCloneResetsRevision()
    {
        $this->doc->setRevision('112233');
        $this->assertEquals('112233', $this->doc->getRevision());

        $clone = clone $this->doc;
        $this->assertEmpty($clone->getRevision());
    }

    public function testCloneDoesNotResetChangedFlag()
    {
        $this->doc->setChanged(true);
        $this->assertTrue($this->doc->getChanged());

        $clone = clone $this->doc;
        $this->assertTrue($clone->getChanged());

        $this->doc->setChanged(false);
        $this->assertFalse($this->doc->getChanged());

        $clone = clone $this->doc;
        $this->assertFalse($clone->getChanged());
    }

    // ==================================================================
    //
    // Following: tests for method ::set()
    //
    // ------------------------------------------------------------------

    /**
     * @expectedException triagens\ArangoDb\ClientException
     */
    public function testSetThrowsExceptionWhenKeyArgumentIsNotAString()
    {
        $this->doc->set(123456, 'Fooo');
    }

    public function testSetEntryIdSetsInternalId()
    {
        $this->doc->set(Document::ENTRY_ID, 'foo/bar');
        $this->assertEquals('foo/bar', $this->doc->getInternalId());
    }

    public function testSetEntryKeySetsInternalKey()
    {
        $this->doc->set(Document::ENTRY_KEY, 'foo');
        $this->assertEquals('foo', $this->doc->getInternalKey());
    }

    public function testSetEntryRevSetsRevision()
    {
        $this->doc->set(Document::ENTRY_REV, '123456');
        $this->assertEquals('123456', $this->doc->getRevision());
    }

    public function testSetEntryIsNewSetsIsNew()
    {
        $this->doc->set(Document::ENTRY_ISNEW, true);
        $this->assertTrue($this->doc->getIsNew());
    }

    public function testSetAttributeSetsChangedFlag()
    {
        $this->doc->set('foo', 'bar');
        $this->assertTrue($this->doc->getChanged());
    }

    public function testCanSetAndGetAttribute()
    {
        $this->doc->set('foo', 'bar');
        $this->assertEquals('bar', $this->doc->get('foo'));
    }

    public function testPropertySetterDelegatesToSet()
    {
        $this->doc->foo = 'bar';
        $this->assertEquals('bar', $this->doc->get('foo'));
        $this->assertTrue($this->doc->getChanged());
    }

    // ==================================================================
    //
    // Following: tests for method ::get()
    //
    // ------------------------------------------------------------------

    public function testGetReturnsNullAsDefault()
    {
        $this->assertEquals(null, $this->doc->get('foo'));
    }

    public function testPropertyGetterDelegatesToGet()
    {
        $this->assertEquals(null, $this->doc->foo);
    }
}