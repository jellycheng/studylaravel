<?php namespace Illuminate\Contracts\Encryption;
//加密解密接口
interface Encrypter {

	/**
	 * Encrypt the given value.
	 * 加密
	 * @param  string  $value
	 * @return string
	 */
	public function encrypt($value);

	/**
	 * Decrypt the given value.
	 * 解密
	 * @param  string  $payload
	 * @return string
	 */
	public function decrypt($payload);

	/**
	 * Set the encryption mode.
	 * 设置模式
	 * @param  string  $mode
	 * @return void
	 */
	public function setMode($mode);

	/**
	 * Set the encryption cipher.
	 * 设置暗号
	 * @param  string  $cipher
	 * @return void
	 */
	public function setCipher($cipher);

}
