<?php
/**
 * Bitcoin Address Generator (BitcoinAddressGenerator) v1.0
 *
 * A PHP library for generating virtually any Bitcoin address format along with their private and public keys.
 *
 * Bitcoin Address Generator is a PHP class designed to facilitate the generation
 * of Bitcoin Wallets and various Bitcoin address formats, including P2PKH, P2SH, P2WPKH,
 * and P2WSH, along with their corresponding uncompressed and compressed private and 
 * public keys.
 * This class employs custom-built functions to handle ECDSA (Elliptic Curve Digital
 * Signature Algorithm) key pair generation on the secp256k1 curve, which is the 
 * cryptographic algorithm behind Bitcoin addresses.
 * The custom functions ensure precise control over the elliptic curve operations, 
 * enabling the creation of Bitcoin addresses from scratch. 
 * The class provides methods to generate new private and public keys and convert
 * them to virtually any Bitcoin address format ensuring flexibility across different
 * Bitcoin applications.
 *
 * Copyright (C) 2024 under Apache License, Version 2.0
 *
 * @author Luca Soltoggio
 * https://www.lucasoltoggio.it
 * https://github.com/toggio/BitcoinAddressGenerator
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

class BitcoinAddressGenerator {
	private $privateKey;
	private $publicKey;
	private $compPublicKey;
	private $x,$y,$c;

	/**
	 * Constructor: Initializes Base58 characters and generates a key resource.
	 */
	public function __construct() {
		$this->newKeys();
	}
	
	/**
	 * Generates a new set of cryptographic keys or uses a known key to generate the corresponding keys.
	 * This method first checks if a known private key is provided. If not, it generates a new
	 * cryptographically secure 256-bit private key. If a provided key is invalid (either not the correct
	 * length or not a proper hexadecimal string), the method hashes the key to produce a valid 256-bit 
	 * private key. It then uses this private key to find the corresponding public keys based on the 
	 * SECP256k1 elliptic curve.
	 *
	 * @param string|null $knownKey Optionally provide a known private key in hexadecimal format.
	 * If false, a new strong cryptographically secure 256-bit private key is generated using random_bytes 
	 * and converted to hexadecimal.
	 */
	public function newKeys($knownKey = null) {
		$this->privateKey = $knownKey ? $knownKey : bin2hex(random_bytes(32));
		if (strlen($this->privateKey) != 64 || @hex2bin($this->privateKey) == false) $this->privateKey=(hash('sha256',$this->privateKey));
		$this->secp256k1_pfind($this->privateKey);
		$this->publicKey = $this->getPublicKey();
		$this->compPublicKey = $this->getCompPublicKey();
		return $knownKey ? true : false;
	}

	/**
	 * Retrieves the private key.
	 *
	 * @return string Hexadecimal private key.
	 */
	public function getPrivateKey() {
		return $this->privateKey;
	}

	/**
	 * Retrieves the uncompressed public key.
	 *
	 * @return string Hexadecimal uncompressed public key.
	 */
	public function getPublicKey() {
		return '04' . str_pad($this->x, 64, '0', STR_PAD_LEFT) . str_pad($this->y, 64, '0', STR_PAD_LEFT);
	}

	/**
	 * Retrieves the compressed public key.
	 *
	 * @return string Hexadecimal compressed public key.
	 */
	public function getCompPublicKey() {
		return $this->c.str_pad($this->x, 64, '0', STR_PAD_LEFT);;
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
	private function convertHexTo5BitArray($hexStr) {
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
	private function bech32_polymod($values) {
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
	private function bech32_hrp_expand($hrp) {
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
	private function bech32_create_checksum($data, $hrp) {
		$values = array_merge($this->bech32_hrp_expand($hrp), $data, [0, 0, 0, 0, 0, 0]);
		$polymod = $this->bech32_polymod($values) ^ 1;
		$checksum = [];
		for ($i = 0; $i < 6; ++$i) {
			$checksum[] = ($polymod >> (5 * (5 - $i))) & 31;
		}
		return $checksum;
	}
	/**
	 * Calculates the multiplicative inverse of a number modulo p.
	 *
	 * @param GMP|string|int $x The number to find the inverse of.
	 * @param GMP|string|int $p The modulus.
	 * @return GMP The multiplicative inverse.
	 */
	private function secp256k1_inverse($x, $p) {
		$inv1 = gmp_init(1);
		$inv2 = gmp_init(0);
		
		while (gmp_cmp($p, 0) != 0 && gmp_cmp($p, 1) != 0) {
			list($inv1, $inv2) = array(
				$inv2,
				gmp_sub($inv1, gmp_mul($inv2, gmp_div_q($x, $p)))
			);
			list($x, $p) = array(
				$p,
				gmp_mod($x, $p)
			);
		}
		return $inv2;
	}
	
	/**
	 * Doubles a point on the SECP256k1 curve.
	 *
	 * @param array|null $point The point to double.
	 * @param GMP|string|int $p The prime modulus of the curve.
	 * @return array|null The doubled point.
	 */
	private function secp256k1_dblpt($point, $p) {
		if (is_null($point)) return null;
		list($x, $y) = $point;
		if (gmp_cmp($y, "0") == 0) return null;
		
		$slope = gmp_mul(gmp_mul("3", gmp_pow($x, 2)), $this->secp256k1_inverse(gmp_mul("2", $y), $p));
		$slope = gmp_mod($slope, $p);
		
		$xsum = gmp_sub(gmp_mod(gmp_pow($slope, 2), $p), gmp_mul("2", $x));
		$ysum = gmp_sub(gmp_mul($slope, gmp_sub($x, $xsum)), $y);
    
		return array(gmp_mod($xsum, $p), gmp_mod($ysum, $p));
	}
	
	/**
	 * Adds two points on the SECP256k1 curve.
	 *
	 * @param array|null $p1 The first point.
	 * @param array|null $p2 The second point.
	 * @param GMP|string|int $p The prime modulus of the curve.
	 * @return array|null The sum of the points.
	 */
	private function secp256k1_addpt($p1, $p2, $p) {
		if ($p1 === null || $p2 === null) return null;
		
		list($x1, $y1) = $p1;
		list($x2, $y2) = $p2;
		
		if (gmp_cmp($x1, $x2) == 0) return $this->secp256k1_dblpt($p1, $p);
		
		$slope = gmp_mul(gmp_sub($y1, $y2), $this->secp256k1_inverse(gmp_sub($x1, $x2), $p));
		$slope = gmp_mod($slope, $p);
		
		$xsum = gmp_sub(gmp_mod(gmp_pow($slope, 2), $p), gmp_add($x1, $x2));
		$ysum = gmp_sub(gmp_mul($slope, gmp_sub($x1, $xsum)), $y1);
		
		return array(gmp_mod($xsum, $p), gmp_mod($ysum, $p));
	}

	/**
	 * Multiplies a point on the SECP256k1 curve by a scalar.
	 *
	 * @param array $pt The point to multiply.
	 * @param GMP|string|int $a The scalar to multiply the point by.
	 * @param GMP|string|int $p The prime modulus of the curve.
	 * @return array|null The resulting point.
	 */
	private function secp256k1_ptmul($pt, $a, $p) {
		$scale = $pt;
		$acc = null;
		
		while (gmp_cmp($a, "0") != 0) {
			if (gmp_mod($a, 2) == 1) {
				$acc = $acc === null ? $scale : $this->secp256k1_addpt($acc, $scale, $p);
			}
			$scale = $this->secp256k1_dblpt($scale, $p);
			$a = gmp_div_q($a, 2);
		}
		return $acc;
	}
	
	/**
	 * Finds the public key from a given private key hex string on the SECP256k1 curve.
	 *
	 * @param string $privateKey The private key in hexadecimal form.
	 */
	private function secp256k1_pfind($privateKey) {
		$p      = gmp_init("0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEFFFFFC2F", 16);
		$Gx     = gmp_init("0x79BE667EF9DCBBAC55A06295CE870B07029BFCDB2DCE28D959F2815B16F81798", 16);
		$Gy     = gmp_init("0x483ADA7726A3C4655DA4FBFC0E1108A8FD17B448A68554199C47D08FFB10D4B8", 16);
		$g      = array($Gx, $Gy);
		
		$n      = gmp_init($privateKey, 16);
		$pair   = $this->secp256k1_ptmul($g, $n, $p);
		
		$this->x = gmp_strval($pair[0], 16);
		$this->y = gmp_strval($pair[1], 16);
		$this->c = (gmp_mod($pair[1], 2) == 0) ? '02' : '03';
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
	private function hexToSAddress($hexKey, $hrp = "bc", $witness = 0, $alt = false) {
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
