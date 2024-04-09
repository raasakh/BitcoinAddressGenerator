<?php
/**
 * Bitcoin Key Generator (BtcKeyGen) v0.9
 *
 * A PHP library for generating various Bitcoin address formats along with their private and public keys 
 *
 * Bitcoin Address Generator is a PHP class designed to facilitate the generation
 * of various Bitcoin address formats, including P2PKH, P2SH, P2WPKH, and P2WSH,
 * along with their corresponding uncompressed and compressed private and public keys.
 * This class utilizes the OpenSSL library to generate ECDSA (Elliptic Curve Digital
 * Signature Algorithm) key pairs on the secp256k1 curve, which is the cryptographic 
 * algorithm behind Bitcoin addresses. The class offers methods to generate new key 
 * pairs, retrieve private and public keys, convert keys to various Bitcoin address 
 * formats, and encode data in Base58Check and Bech32 encoding schemes.
 *
 * Copyright (C) 2024 under Apache License, Version 2.0
 *
 * @author Luca Soltoggio
 * https://www.lucasoltoggio.it
 * https://github.com/toggio/BitcoinKeyGenerator
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 */

class BtcKeyGen {
	private $keyResource;
	private $privateKey;
	private $publicKey;
	private $compPublicKey;

	/**
	 * Constructor: Initializes Base58 characters and generates a key resource.
	 */
	public function __construct() {
		$this->newKey();
	}
	
	/**
	 * Generates a new OpenSSL key resource based on predefined configurations.
	 */
	private function newKey() {
		$config = [
			'private_key_type' => OPENSSL_KEYTYPE_EC,
			'curve_name' => 'secp256k1'
		];
		$this->keyResource = openssl_pkey_new($config);
		$this->privateKey = $this->getPrivateKey();
		$this->publicKey = $this->getPublicKey();
		$this->compPublicKey = $this->getCompPublicKey();
	}

	/**
	 * Extracts the private key from the OpenSSL key resource in hexadecimal format.
	 *
	 * @return string Hexadecimal private key.
	 */
	public function getPrivateKey() {
		openssl_pkey_export($this->keyResource, $privateKey);
		$beginMarker = "-----BEGIN PRIVATE KEY-----";
		$endMarker = "-----END PRIVATE KEY-----";
		$binaryKey = bin2hex(base64_decode(trim(str_replace([$beginMarker, $endMarker], '', $privateKey))));
		return substr($binaryKey, strpos($binaryKey, '0420') + 4, 64);
	}

	/**
	 * Retrieves the uncompressed public key from the OpenSSL key resource.
	 *
	 * @return string Hexadecimal uncompressed public key.
	 */
	public function getPublicKey() {
		$keyDetails = openssl_pkey_get_details($this->keyResource);
		$x = bin2hex(substr($keyDetails["ec"]["x"], 0, 32));
		$y = bin2hex(substr($keyDetails["ec"]["y"], 0, 32));
		return '04' . $x . $y;
	}

	/**
	 * Retrieves the compressed public key from the OpenSSL key resource.
	 *
	 * @return string Hexadecimal compressed public key.
	 */
	public function getCompPublicKey() {
		$keyDetails = openssl_pkey_get_details($this->keyResource);
		$x = bin2hex(substr($keyDetails["ec"]["x"], 0, 32));
		$yLastByte = bin2hex(substr($keyDetails["ec"]["y"], -1));
		return ((hexdec($yLastByte) % 2 == 0) ? '02' : '03') . $x;
	}

	/**
	 * Encodes a hexadecimal string into Base58.
	 *
	 * @param string $hex Hexadecimal string to encode.
	 * @return string Encoded Base58 string.
	 */
	private function encodeBase58($hex) {
		$base58Chars = array_merge(range('1', '9'), range('A', 'H'), range('J', 'N'), range('P', 'Z'), range('a', 'k'), range('m', 'z'));
		$zeros = substr($hex, 0, 2) === "00";
		$n = gmp_init(strtoupper($hex), 16);
		$encoded = '';
		while (gmp_cmp($n, 0) > 0) {
			list($n, $rem) = gmp_div_qr($n, 58);
			$encoded = $base58Chars[gmp_intval($rem)] . $encoded;
		}
		return $zeros ? "1" . $encoded : $encoded;
	}

	/**
	 * Calculates the checksum for a given hexadecimal string.
	 *
	 * @param string $hex Hexadecimal string to calculate checksum for.
	 * @return string 8-character hexadecimal checksum.
	 */
	private function checksum($hex) {
		$binaryData = pack("H*", $hex);
		$hash = hash('sha256', hash('sha256', $binaryData, true), true);
		return substr(bin2hex($hash), 0, 8);
	}

	/**
	 * Performs a RIPEMD-160 hash after a SHA-256 hash on the given data.
	 *
	 * @param string $data Data to hash.
	 * @return string Hexadecimal hash160 string.
	 */
	private function hash160($data) {
		return bin2hex(pack("H*", hash('ripemd160', hash('sha256', $data, true))));
	}

	/**
	 * Converts a hexadecimal key and prefix into a Base58Check address.
	 *
	 * @param string $hexKey The hexadecimal representation of the key.
	 * @param string $preHex The prefix for the address type (e.g., '00' for P2PKH).
	 * @param bool $hashing Determines if the $hexKey should be hashed.
	 * @return string The Base58Check encoded address.
	 */
	private function hexToAddress($hexKey, $preHex, $hashing = false) {
		if ($hashing) {
			$hexKey = $this->hash160(hex2bin($hexKey));
		}
		$address = $this->encodeBase58($preHex . $hexKey . $this->checksum($preHex . $hexKey));
		return $address;
	}

	/**
	 * Converts a private key in hexadecimal format to a Wallet Import Format (WIF) string.
	 * If no key is provided, uses the instance's privateKey.
	 *
	 * @param string|null $hexKey Optional. The private key in hexadecimal format. Default to instance's privateKey.
	 * @return string The WIF encoded private key.
	 */
	public function getWIF($hexKey = null) {
		if ($hexKey === null) $hexKey = $this->privateKey;
		return $this->hexToAddress($hexKey, "80");
	}
	
	/**
	 * Converts a private key in hexadecimal format to a WIF compressed string.
	 * If no key is provided, uses the instance's privateKey.
	 *
	 * @param string|null $hexKey Optional. The private key in hexadecimal format. Default to instance's privateKey.
	 * @return string The WIF encoded compressed private key.
	 */	
	public function getCompWIF($hexKey = null) {
		if ($hexKey === null) $hexKey = $this->privateKey;
		return $this->hexToAddress($hexKey."01", "80");
	}

	/**
	 * Generates a Pay-to-PubKey-Hash (P2PKH) address from a public key.
	 * If no key is provided, uses the instance's publicKey.
	 *
	 * @param string|null $hexKey Optional. The public key in hexadecimal format. Default to instance's publicKey.
	 * @return string The P2PKH address.
	 */
	public function getP2PKH($hexKey = null) {
		if ($hexKey === null) $hexKey = $this->publicKey;
		return $this->hexToAddress($hexKey, "00", true);
	}
	
	/**
	 * Generates a Pay-to-PubKey-Hash (P2PKH) compressed address from a compressed public key.
	 * If no key is provided, uses the instance's compPublicKey.
	 *
	 * @param string|null $hexKey Optional. The compressed public key in hexadecimal format. Default to instance's compPublicKey.
	 * @return string The P2PKH compressed address.
	 */
	public function getCompP2PKH($hexKey = null) {
		if ($hexKey === null) $hexKey = $this->compPublicKey;
		return $this->hexToAddress($hexKey, "00", true);
	}

	/**
	 * Generates a Pay-to-Script-Hash (P2SH) address from a compressed public key.
	 * If no key is provided, uses the instance's compPublicKey.
	 * 
	 * @param string|null $hexKey Optional. The compressed public key in hexadecimal format. Default to instance's compPublicKey.
	 * @return string The P2SH address.
	 */
	public function getP2SH($hexKey = null) {
		if ($hexKey === null) $hexKey = $this->compPublicKey;
		$hexKey = "0014" . $this->hash160(hex2bin($hexKey)); // P2SH(P2WPKH) script prefix
		return $this->hexToAddress($hexKey, "05", true);
	}

	/**
	 * Converts a hexadecimal string into an array of 5-bit integers.
	 *
	 * @param string $hexStr The hexadecimal string to convert.
	 * @return array An array of 5-bit integers.
	 */
	public function convertHexTo5BitArray($hexStr) {
		$binaryStr = '';
		foreach (str_split($hexStr) as $hexChar) {
			$binaryStr .= str_pad(base_convert($hexChar, 16, 2), 4, '0', STR_PAD_LEFT);
		}
		$binaryStr = str_pad($binaryStr, ceil(strlen($binaryStr) / 5) * 5, '0', STR_PAD_LEFT);
		$chunksOf5Bits = str_split($binaryStr, 5);
		return array_map('bindec', $chunksOf5Bits);
	}

	/**
	 * Calculates the polymod of a value array for Bech32 checksum generation.
	 *
	 * @param array $values The value array.
	 * @return int The polymod.
	 */
	public function bech32_polymod($values) {
		$generator = [0x3b6a57b2, 0x26508e6d, 0x1ea119fa, 0x3d4233dd, 0x2a1462b3];
		$chk = 1;
		foreach ($values as $value) {
			$top = $chk >> 25;
			$chk = ($chk & 0x1ffffff) << 5 ^ $value;
			for ($i = 0; $i < 5; ++$i) {
				if ($top >> $i & 1) {
					$chk ^= $generator[$i];
				}
			}
		}
		return $chk;
	}

	/**
	 * Expands a human-readable part (HRP) for use in Bech32 checksum computation.
	 *
	 * @param string $hrp The human-readable part.
	 * @return array The expanded HRP.
	 */
	public function bech32_hrp_expand($hrp) {
		$expand = [];
		foreach (str_split($hrp) as $char) {
			$expand[] = ord($char) >> 5;
		}
		$expand[] = 0;
		foreach (str_split($hrp) as $char) {
			$expand[] = ord($char) & 31;
		}
		return $expand;
	}

	/**
	 * Generates a Bech32 checksum for given data and HRP.
	 *
	 * @param array $data The data array.
	 * @param string $hrp The human-readable part.
	 * @return array The checksum.
	 */
	public function bech32_create_checksum($data, $hrp) {
		$values = array_merge($this->bech32_hrp_expand($hrp), $data, [0, 0, 0, 0, 0, 0]);
		$polymod = $this->bech32_polymod($values) ^ 1;
		$checksum = [];
		for ($i = 0; $i < 6; ++$i) {
			$checksum[] = ($polymod >> (5 * (5 - $i))) & 31;
		}
		return $checksum;
	}
	
	/**
	 * Generates either a SegWit (bech32) address from a hex key, with the option for P2WSH format.
	 *
	 * @param string $hexKey The hexadecimal key.
	 * @param string $hrp Human-readable part, defaults to "bc" for Bitcoin mainnet.
	 * @param int $witness Witness version, defaults to 0.
	 * @param bool $alt Indicates whether to generate a P2WSH (true) or P2WPKH (false) address.
	 * @return string The generated bech32 address.
	 */
	public function hexToSAddress($hexKey, $hrp = "bc", $witness = 0, $alt = false) {
		$charset = 'qpzry9x8gf2tvdw0s3jn54khce6mua7l';
		$data = hex2bin($hexKey);
		$step1 = $alt ? bin2hex(hash('sha256', $data, true)) . "0" : $this->hash160($data);
		$step2 = $this->convertHexTo5BitArray($step1);
		$checksum = $this->bech32_create_checksum(array_merge([$witness], $step2), $hrp);
		$step3 = $alt ? array_merge($step2, $checksum) : array_merge([$witness], $step2, $checksum);
		$step4 = '';

		foreach ($step3 as $value) {
			if ($value < 0 || $value >= strlen($charset)) {
				throw new Exception("Value out of range: $value");
			}
			$step4 .= $charset[$value];
		}

		return $hrp . "1" . $step4;
	}

	/**
	 * Generate a Pay-to-Witness-Public-Key-Hash (P2WPKH) address from a compressed public key.
	 * If no key is provided, uses the instance's compPublicKey.
	 *
	 * @param string|null $hexKey Optional. The compressed public key in hexadecimal format. Default to instance's compPublicKey.
	 * @return string The P2WPKH address.
	 */
	public function getP2WPKH($hexKey = null) {
		if ($hexKey === null) $hexKey = $this->compPublicKey;
		return $this->hexToSAddress($hexKey);
	}

	/**
	 * Generate a Pay-to-Witness-Script-Hash (P2WSH) address from a compressed public key.
	 * If no key is provided, uses the instance's compPublicKey.
	 *
	 * @param string $hexKey The public key in hexadecimal format.
	 * @return string|null $hexKey Optional. The compressed public key in hexadecimal format. Default to instance's compPublicKey.
	 */
	public function getP2WSH($hexKey = null) {
		if ($hexKey === null) $hexKey = $this->compPublicKey;
		return $this->hexToSAddress($hexKey, "bc", 0, true);
	}
}
?>
