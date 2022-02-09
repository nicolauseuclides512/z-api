<?php
/**
 * @author Jehan Afwazi Ahmad <jee.archer@gmail.com>.
 */


namespace App\Models;

use App\Exceptions\AppException;
use Firebase\JWT\JWT;
use Illuminate\Http\Response;
use Laravel\Passport\Passport;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\ValidationData;

class AuthToken
{
    protected $id;
    protected $username;
    protected $fullName;
    protected $email;

    protected $organizationId;
    protected $organizationName;
    protected $organizationPortal;
    protected $organizationLogo;
    protected $address;
    protected $phone;
    protected $countryId;
    protected $provinceId;
    protected $districtId;
    protected $regionId;
    protected $zip;

    protected $expired;
    protected $application;
    public static $rawToken;
    protected static $scopes;

    public static $info;

    public function __construct()
    {
        $this->publicKey = file_get_contents(Passport::keyPath('oauth-public.key'));
        $this->secretKey = file_get_contents(Passport::keyPath('oauth-private.key'));
    }

    public static function inst()
    {
        return new self();
    }

    /**
     * @param $token
     * @return bool
     * @throws \Exception
     */
    public function verify($token)
    {
        try {
            self::$rawToken = $token;

            $tokenData = (new Parser())->parse(self::$rawToken);

            if ($tokenData->validate(new ValidationData())) {
                $matchAlgKey = "";
                if ($tokenData->getHeader('alg') === 'HS256' &&
                    $tokenData->verify(new \Lcobucci\JWT\Signer\Hmac\Sha256(), $this->secretKey)) {
                    $matchAlgKey = $this->secretKey;
                } elseif ($tokenData->getHeader('alg') === 'RS256' &&
                    $tokenData->verify(new \Lcobucci\JWT\Signer\Rsa\Sha256(), $this->publicKey)) {
                    $matchAlgKey = $this->publicKey;
                }

                if (empty($matchAlgKey))
                    throw AppException::inst(
                        'AlgKey not found',
                        Response::HTTP_UNPROCESSABLE_ENTITY);

                $response = JWT::decode($token, $matchAlgKey, [$tokenData->getHeader('alg')]);

                $this->id = $response->data->userId;
                $this->username = $response->data->username;
                $this->email = $response->data->email;
                $this->fullName = $response->data->fullName;
                $this->organizationId = $response->data->organizationId;
                $this->organizationName = $response->data->organizationName;
                $this->organizationPortal = $response->data->organizationPortal;
                $this->organizationLogo = $response->data->organizationLogo;
                $this->application = $response->data->application;
                $this->address = $response->data->address;
                $this->phone = $response->data->phone;
                $this->countryId = $response->data->countryId;
                $this->provinceId = $response->data->provinceId;
                $this->districtId = $response->data->districtId;
                $this->regionId = $response->data->regionId;
                $this->zip = $response->data->zip;

                self::$scopes = $response->scopes;

                self::$info = $response->data;

                return true;
            }


            return false;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return mixed
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * @param mixed $fullName
     */
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getOrganizationId()
    {
        return $this->organizationId;
    }

    /**
     * @param mixed $organizationId
     */
    public function setOrganizationId($organizationId)
    {
        $this->organizationId = $organizationId;
    }

    /**
     * @return mixed
     */
    public function getOrganizationName()
    {
        return $this->organizationName;
    }

    /**
     * @param mixed $organizationName
     */
    public function setOrganizationName($organizationName)
    {
        $this->organizationName = $organizationName;
    }

    /**
     * @return mixed
     */
    public function getOrganizationPortal()
    {
        return $this->organizationPortal;
    }

    /**
     * @param mixed $organizationPortal
     */
    public function setOrganizationPortal($organizationPortal)
    {
        $this->organizationPortal = $organizationPortal;
    }

    /**
     * @return mixed
     */
    public function getOrganizationLogo()
    {
        return $this->organizationLogo;
    }

    /**
     * @param mixed $organizationLogo
     */
    public function setOrganizationLogo($organizationLogo)
    {
        $this->organizationLogo = $organizationLogo;
    }

    /**
     * @return mixed
     */
    public function getExpired()
    {
        return $this->expired;
    }

    /**
     * @param mixed $expired
     */
    public function setExpired($expired)
    {
        $this->expired = $expired;
    }

    /**
     * @return mixed
     */
    public static function getScopes()
    {
        return self::$scopes;
    }

    /**
     * @return mixed
     */
    public static function info()
    {
        return self::$info;
    }

    /**
     * @return mixed
     */
    public static function rawToken()
    {
        return self::$rawToken;
    }

    public static function isInventory()
    {
        $info = self::info();
        $application = $info->application ?? 'invoice';
        $isInventory = strtolower($application) == 'inventory' ? true : false;
        return $isInventory;
    }

    /**
     * @return bool|string
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }

    /**
     * @param bool|string $publicKey
     */
    public function setPublicKey($publicKey): void
    {
        $this->publicKey = $publicKey;
    }

    /**
     * @return bool|string
     */
    public function getSecretKey()
    {
        return $this->secretKey;
    }

    /**
     * @param bool|string $secretKey
     */
    public function setSecretKey($secretKey): void
    {
        $this->secretKey = $secretKey;
    }

    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $address
     */
    public function setAddress($address): void
    {
        $this->address = $address;
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param mixed $phone
     */
    public function setPhone($phone): void
    {
        $this->phone = $phone;
    }

    /**
     * @return mixed
     */
    public function getCountryId()
    {
        return $this->countryId;
    }

    /**
     * @param mixed $countryId
     */
    public function setCountryId($countryId): void
    {
        $this->countryId = $countryId;
    }

    /**
     * @return mixed
     */
    public function getProvinceId()
    {
        return $this->provinceId;
    }

    /**
     * @param mixed $provinceId
     */
    public function setProvinceId($provinceId): void
    {
        $this->provinceId = $provinceId;
    }

    /**
     * @return mixed
     */
    public function getDistrictId()
    {
        return $this->districtId;
    }

    /**
     * @param mixed $districtId
     */
    public function setDistrictId($districtId): void
    {
        $this->districtId = $districtId;
    }

    /**
     * @return mixed
     */
    public function getRegionId()
    {
        return $this->regionId;
    }

    /**
     * @param mixed $regionId
     */
    public function setRegionId($regionId): void
    {
        $this->regionId = $regionId;
    }

    /**
     * @return mixed
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * @param mixed $zip
     */
    public function setZip($zip): void
    {
        $this->zip = $zip;
    }

    /**
     * @return mixed
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * @param mixed $application
     */
    public function setApplication($application): void
    {
        $this->application = $application;
    }

    /**
     * @return mixed
     */
    public static function getRawToken()
    {
        return self::$rawToken;
    }

    /**
     * @param mixed $rawToken
     */
    public static function setRawToken($rawToken): void
    {
        self::$rawToken = $rawToken;
    }

    /**
     * @return mixed
     */
    public static function getInfo()
    {
        return self::$info;
    }

    /**
     * @param mixed $info
     */
    public static function setInfo(array $info): void
    {
        self::$info = (object)$info;
    }


}
