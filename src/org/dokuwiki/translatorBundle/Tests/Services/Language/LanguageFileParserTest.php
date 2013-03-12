<?php
namespace org\dokuwiki\translatorBundle\Services\Language;

class LanguageFileParserTestDummy extends LanguageFileParser {
    public function setContent($content) {
        $this->content = $content;
    }

    public function getContent() {
        return $this->content;
    }

    public function setAuthor($author) {
        $this->author = $author;
    }

    public function getAuthor() {
        return $this->author;
    }
}

class LanguageFileParserTest extends \PHPUnit_Framework_TestCase {

    function testGoToStart() {
        $parser = new LanguageFileParserTestDummy();

        $parser->setContent('<?php');
        $parser->goToStart();
        $this->assertEquals('', $parser->getContent());

        $parser->setContent('some Text<?php');
        $parser->goToStart();
        $this->assertEquals('', $parser->getContent());

        $parser->setContent('<?phpa');
        $parser->goToStart();
        $this->assertEquals('a', $parser->getContent());

        $parser->setContent('<?php<?php');
        $parser->goToStart();
        $this->assertEquals('<?php', $parser->getContent());
    }

    function testDetermineNextMode() {
        $parser = new LanguageFileParserTestDummy();

        $parser->setContent('/* Bla bla */');
        $this->assertEquals(LanguageFileParser::$MODE_COMMENT_MULTI_LINE, $parser->determineNextMode());
        $this->assertEquals(' Bla bla */', $parser->getContent());

        $parser->setContent('// Bla');
        $this->assertEquals(LanguageFileParser::$MODE_COMMENT_SINGLE_LINE, $parser->determineNextMode());
        $this->assertEquals(' Bla', $parser->getContent());

        $parser->setContent('$lang["some"] = "text";');
        $this->assertEquals(LanguageFileParser::$MODE_LANG, $parser->determineNextMode());
        $this->assertEquals('"some"] = "text";', $parser->getContent());

        $parser->setContent('$lang[\'js\']["some"] = "text";');
        $this->assertEquals(LanguageFileParser::$MODE_LANG, $parser->determineNextMode());
        $this->assertEquals('\'js\']["some"] = "text";', $parser->getContent());

        $parser->setContent('echo "bla";');
        $this->assertEquals(LanguageFileParser::$MODE_PHP_UNKNOWN, $parser->determineNextMode());
        $this->assertEquals('echo "bla";', $parser->getContent());
    }

    function testProcessMultiLineComment() {
        $parser = new LanguageFileParserTestDummy();

        $parser->setContent("some text\n * @var string some text\n   * @author some one üß <email.address@someone>\n*/");
        $this->assertEquals(LanguageFileParser::$MODE_PHP, $parser->processMultiLineComment());
        $this->assertEquals(array('some one üß' => 'email.address@someone'), $parser->getAuthor());
        $this->assertEquals('', $parser->getContent());

        $parser->setAuthor(array());
        $parser->setContent("some text\n * @var string some text\n   * @author some one üß <email.address@someone>\n* @author an other <email.some@bla>\n*/ text");
        $this->assertEquals(LanguageFileParser::$MODE_PHP, $parser->processMultiLineComment());
        $expected = array('some one üß' => 'email.address@someone', 'an other' => 'email.some@bla');
        $this->assertEquals($expected, $parser->getAuthor());
        $this->assertEquals(' text', $parser->getContent());

        $parser->setAuthor(array());
        $parser->setContent("some text\n * @var string some text\n\n*/");
        $this->assertEquals(LanguageFileParser::$MODE_PHP, $parser->processMultiLineComment());
        $this->assertEquals(array(), $parser->getAuthor());
        $this->assertEquals('', $parser->getContent());
    }

    /**
     * @expectedException \org\dokuwiki\translatorBundle\Services\Language\LanguageParseException
     */
    function testProcessMultiLineCommentParserException() {
        $parser = new LanguageFileParserTestDummy();
        $parser->setAuthor(array());
        $parser->setContent("some text\n * @var string some text\n\n");
        $parser->processMultiLineComment();
    }

    function testProcessSingleLineComment() {
        $parser = new LanguageFileParserTestDummy();

        $parser->setContent(" hello you");
        $this->assertEquals(LanguageFileParser::$MODE_PHP, $parser->processSingleLineComment());
        $this->assertEquals('', $parser->getContent());

        $parser->setContent(" hello you\nmore php code");
        $this->assertEquals(LanguageFileParser::$MODE_PHP, $parser->processSingleLineComment());
        $this->assertEquals('more php code', $parser->getContent());
    }

}