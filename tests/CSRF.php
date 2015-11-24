<?php

namespace Volnix\CSRF\Tests;
use Volnix\CSRF\CSRF as CSRFTokenGenerator;

class CSRF extends \PHPUnit_Framework_TestCase
{
	const ALT_TOKEN_NAME = "foo_bar_1234567890";

	public function __construct()
	{
		ob_start();
	}

	public function __destruct()
	{
		print ob_get_clean();
	}

	public function setUp()
	{
		// Suppress errors on session_start() in test
		// http://stackoverflow.com/questions/23270650/cannot-send-session-cookie-headers-already-sent-phpunit-laravel
		@session_start();
		$this->_killSession();
	}

	public function tearDown()
	{
		$this->_killSession();
		session_write_close();
	}

	public function testGenerateToken()
	{
		CSRFTokenGenerator::generateToken();
		$this->assertNotNull($_SESSION[CSRFTokenGenerator::TOKEN_NAME]);

		CSRFTokenGenerator::generateToken(self::ALT_TOKEN_NAME);
		$this->assertNotNull($_SESSION[self::ALT_TOKEN_NAME]);
	}

	public function testGetToken()
	{
		// basic
		CSRFTokenGenerator::generateToken();
		$this->assertEquals($_SESSION[CSRFTokenGenerator::TOKEN_NAME], CSRFTokenGenerator::getToken());

		CSRFTokenGenerator::generateToken(self::ALT_TOKEN_NAME);
		$this->assertEquals($_SESSION[self::ALT_TOKEN_NAME], CSRFTokenGenerator::getToken(self::ALT_TOKEN_NAME));

		$this->_killSession();

		// no token set, should get set without explicit generate call
		$this->assertNotNull(CSRFTokenGenerator::getToken());
		$this->assertNotNull(CSRFTokenGenerator::getToken(self::ALT_TOKEN_NAME));

		$this->assertEquals($_SESSION[CSRFTokenGenerator::TOKEN_NAME], CSRFTokenGenerator::getToken());
		$this->assertEquals($_SESSION[self::ALT_TOKEN_NAME], CSRFTokenGenerator::getToken(self::ALT_TOKEN_NAME));
	}

	public function testValidate()
	{
		// good validation
		$post_data = array(CSRFTokenGenerator::TOKEN_NAME => CSRFTokenGenerator::getToken());
		$this->assertTrue(CSRFTokenGenerator::validate($post_data));

		$post_data = array(self::ALT_TOKEN_NAME => CSRFTokenGenerator::getToken(self::ALT_TOKEN_NAME));
		$this->assertTrue(CSRFTokenGenerator::validate($post_data, self::ALT_TOKEN_NAME));

		// bad validation
		$this->_killSession();

		$post_data = array(CSRFTokenGenerator::TOKEN_NAME => "bad_token_data");
		$this->assertFalse(CSRFTokenGenerator::validate($post_data));

		$post_data = array(self::ALT_TOKEN_NAME => "bad_token_data");
		$this->assertFalse(CSRFTokenGenerator::validate($post_data, self::ALT_TOKEN_NAME));

		$post_data = array("bad_token_name" => CSRFTokenGenerator::getToken());
		$this->assertFalse(CSRFTokenGenerator::validate($post_data));
	}

	public function testGetHiddenInput()
	{
		$token = CSRFTokenGenerator::getToken();
		$this->assertEquals(sprintf('<input type="hidden" name="%s" value="%s"/>', CSRFTokenGenerator::TOKEN_NAME, $token), CSRFTokenGenerator::getHiddenInputString());

		$token = CSRFTokenGenerator::getToken(self::ALT_TOKEN_NAME);
		$this->assertEquals(sprintf('<input type="hidden" name="%s" value="%s"/>', self::ALT_TOKEN_NAME, $token), CSRFTokenGenerator::getHiddenInputString(self::ALT_TOKEN_NAME));
	}

	public function testGetQueryString()
	{
		$token = CSRFTokenGenerator::getToken();
		$this->assertEquals(sprintf('%s=%s', CSRFTokenGenerator::TOKEN_NAME, $token), CSRFTokenGenerator::getQueryString());

		$token = CSRFTokenGenerator::getToken(self::ALT_TOKEN_NAME);
		$this->assertEquals(sprintf('%s=%s', self::ALT_TOKEN_NAME, $token), CSRFTokenGenerator::getQueryString(self::ALT_TOKEN_NAME));
	}

	public function testGetAsArray()
	{
		$token  = CSRFTokenGenerator::getToken();

	}

	private function _killSession()
	{
		$_SESSION[CSRFTokenGenerator::TOKEN_NAME] = $_SESSION[self::ALT_TOKEN_NAME] = null;
		unset($_SESSION[CSRFTokenGenerator::TOKEN_NAME], $_SESSION[self::ALT_TOKEN_NAME]);
	}
}
