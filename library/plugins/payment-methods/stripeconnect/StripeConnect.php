<?php
require_once dirname(__FILE__) . '/StripeConnectFunctions.php';

class StripeConnect extends PaymentMethodBase
{
    use StripeConnectFunctions;

    public const KEY_NAME = __CLASS__;
    public const PAGE_SIZE = 10;

    private $stripe;
    private $stripeAccountId = '';
    private $stripeAccountType;
    private $requiredFields = [];
    private $userInfoObj;
    private $resp = [];
    private $liveMode = '';
    private $sessionId = '';
    private $priceId = '';
    private $customerId = '';
    private $loginUrl = '';
    private $connectedAccounts = [];
    private $customerInfo = [];
    public $userId = 0;

    public $requiredKeys = [
        'env',
        'client_id',
        'publishable_key',
        'secret_key'
    ];

    private const CONNECT_URI = "https://connect.stripe.com/oauth";
    public const TERMS_AND_SERVICES_URI = "https://stripe.com/en-in/connect-account/legal";

    public const REQUEST_CREATE_ACCOUNT = 1;
    public const REQUEST_RETRIEVE_ACCOUNT = 2;
    public const REQUEST_UPDATE_ACCOUNT = 3;
    public const REQUEST_PERSON_TOKEN = 4;
    public const REQUEST_ADD_BANK_ACCOUNT = 5;
    public const REQUEST_CREATE_PERSON = 6;
    public const REQUEST_UPDATE_PERSON = 7;
    public const REQUEST_UPLOAD_VERIFICATION_FILE = 8;
    public const REQUEST_DELETE_ACCOUNT = 9;
    public const REQUEST_CREATE_SESSION = 10;
    public const REQUEST_CREATE_PRICE = 11;
    public const REQUEST_CREATE_CUSTOMER = 12;
    public const REQUEST_RETRIEVE_CUSTOMER = 13;
    public const REQUEST_UPDATE_CUSTOMER = 14;
    public const REQUEST_CREATE_LOGIN_LINK = 15;
    public const REQUEST_ALL_CONNECT_ACCOUNTS = 16;
    public const REQUEST_INITIATE_REFUND = 17;
    public const REQUEST_TRANSFER_AMOUNT = 18;
    public const REQUEST_REVERSE_TRANSFER = 19;
    public const REQUEST_ADD_CARD = 20;
    public const REQUEST_REMOVE_CARD = 21;
    public const REQUEST_LIST_ALL_CARDS = 22;
    public const REQUEST_CREATE_CARD_TOKEN = 23;
    public const REQUEST_CHARGE = 24;
    public const REQUEST_PAYMENT_INTENT = 25;
    public const REQUEST_RETRIEVE_PAYMENT_INTENT = 26;
    public const REQUEST_CREATE_PAYMENT_METHOD = 27;
    public const REQUEST_CAPTURE_PAYMENT = 28;

    /**
     * __construct
     *
     * @param  int $langId
     * @return void
     */
    public function __construct(int $langId)
    {
        $this->langId = 0 < $langId ? $langId : CommonHelper::getLangId();
        $this->requiredKeys();
    }


    /**
     * requiredKeys
     *
     * @return void
     */
    public function requiredKeys()
    {
        $this->env = FatUtility::int($this->getKey('env'));
        if (Plugin::ENV_PRODUCTION == $this->env) {
            $this->liveMode = "live_";
            $this->requiredKeys = [
                'env',
                $this->liveMode . 'client_id',
                $this->liveMode . 'publishable_key',
                $this->liveMode . 'secret_key'
            ];
        }
    }

    /**
     * init
     *
     * @param int $userId
     * @return void
     */
    public function init(int $userId = 0, bool $isSeller = false)
    {
        if (false == $this->validateSettings()) {
            return false;
        }

        if (0 < $userId) {
            if (false === $isSeller && false === $this->loadLoggedUserInfo($userId)) {
                return false;
            } else if (false === $this->loadSellerInfo($userId)) {
                return false;
            }
        }

        // For Some functions this line is also required to initiate API secret key
        \Stripe\Stripe::setApiKey($this->settings[$this->liveMode . 'secret_key']);

        $this->stripe = new \Stripe\StripeClient($this->settings[$this->liveMode . 'secret_key']);
        return true;
    }

    /**
     * getRedirectUri
     *
     * @return string
     */
    public function getRedirectUri(): string
    {
        return self::CONNECT_URI . "/authorize?response_type=code&client_id=" . $this->settings[$this->liveMode . 'client_id'] . "&scope=read_write&redirect_uri=" . UrlHelper::generateFullUrl(self::KEY_NAME, 'callback', [], '', false);
    }

    /**
     * connect
     *
     * @return bool
     */
    private function connect(): bool
    {
        $params = [
            'clientId'                => $this->settings[$this->liveMode . 'client_id'],
            'clientSecret'            => $this->settings[$this->liveMode . 'secret_key'],
            'redirectUri'             => $this->getRedirectUri(),
            'urlAuthorize'            => self::CONNECT_URI . '/authorize',
            'urlAccessToken'          => self::CONNECT_URI . '/token',
            'urlResourceOwnerDetails' => 'https://api.stripe.com/v1/account'
        ];
        $this->stripe = new \League\OAuth2\Client\Provider\GenericProvider($params);
        return true;
    }

    /**
     * getResponse
     *
     * @return mixed
     */
    public function getResponse()
    {
        return empty($this->resp) ? (object) array() : $this->resp;
    }

    /**
     * register
     *
     * @return bool
     */
    public function register(): bool
    {
        if (empty($this->getAccountId())) {
            if (false === $this->doRequest(self::REQUEST_CREATE_ACCOUNT)) {
                return false;
            }
        }
        return true;
    }

    /**
     * accessAccountId
     *
     * @param string $code
     * @return bool
     */
    public function accessAccountId(string $code): bool
    {
        try {
            $this->connect();
            $accessToken = $this->stripe->getAccessToken('authorization_code', [
                'code' => $code
            ]);
            $this->stripeAccountId = $this->stripe->getResourceOwner($accessToken)->getId();
            return $this->updateUserMeta('stripe_account_id', $this->stripeAccountId);
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * isUserAccountRejected
     *
     * @return bool
     */
    public function isUserAccountRejected(): bool
    {
        if (false === $this->loadRemoteUserInfo()) {
            return false;
        }

        $this->userInfoObj = $this->getResponse();
        $requirements = $this->userInfoObj->requirements;
        if (isset($requirements->disabled_reason) && false !== strpos($requirements->disabled_reason, "rejected")) {
            $this->unsetUserAccountElements();
            $msg = Labels::getLabel('MSG_YOUR_ACCOUNT_HAS_BEEN_', $this->langId);
            $this->error = $msg . ucwords(str_replace(".", " - ", $requirements->disabled_reason));
            return true;
        }
        return false;
    }

    /**
     * unsetUserAccountElements
     *
     * @return type
     */
    private function unsetUserAccountElements(): bool
    {
        FatApp::getDb()->deleteRecords(User::DB_TBL_META, ['smt' => 'usermeta_user_id = ? AND usermeta_key LIKE ? ', 'vals' => [$this->userId, 'stripe_%']]);
        return true;
    }

    /**
     * initialFieldsValue
     *
     * @return array
     */
    public function initialFieldsValue(): array
    {
        return [
            'email' => $this->userData['credential_email'],
            'business_profile' => [
                'name' => $this->userData['shop_name'],
                'url' => UrlHelper::generateFullUrl('shops', 'view', [$this->userData['shop_id']]),
                'support_url' => UrlHelper::generateFullUrl('shops', 'view', [$this->userData['shop_id']]),
                'support_phone' => $this->userData['shop_phone'],
                'support_email' => $this->userData['credential_email'],
                'support_address' => [
                    'city' => $this->userData['shop_city'],
                    'country' => strtoupper($this->userData['country_code']),
                    'line1' => $this->userData['shop_address_line_1'],
                    'line2' => $this->userData['shop_address_line_2'],
                    'postal_code' => $this->userData['shop_postalcode'],
                    'state' => $this->userData['state_code'],
                ],
                'product_description' => $this->userData['shop_description'],
            ]
        ];
    }

    /**
     * createAccount
     *
     * Can follow: https://stripe.com/docs/api OR https://medium.com/@Keithweaver_/creating-your-own-marketplace-with-stripe-connect-php-like-shopify-or-uber-6eadbb08993f for help.
     *
     * @return bool
     */
    public function createAccount(): bool
    {
        $data = [
            'type' => 'custom',
            'country' => strtoupper($this->userData['country_code']),
            'email' => $this->userData['credential_email'],
            'requested_capabilities' => [
                'card_payments',
                'transfers',
            ]
        ];

        if (true === $this->loadBaseCurrencyCode()) {
            $data['default_currency'] = $this->systemCurrencyCode;
        }

        $this->resp = $this->create($data);
        if (false === $this->resp) {
            return false;
        }
        $this->stripeAccountId = $this->resp->id;
        $this->updateUserMeta('stripe_account_type', 'custom');
        return $this->updateUserMeta('stripe_account_id', $this->resp->id);
    }


    /**
     * getPersonToken
     *
     * @return string
     */
    public function getPersonToken(): string
    {
        $personId = $this->getUserMeta('stripe_person_token');
        if (empty($personId)) {
            if (false === $this->createPersonToken()) {
                return false;
            }
            $personId = $this->getUserMeta('stripe_person_token');
        }
        return (string)$personId;
    }

    /**
     * createPersonToken
     *
     * @return string
     */
    private function createPersonToken(): bool
    {
        $this->resp = $this->createToken();
        if (false === $this->resp) {
            return false;
        }
        return $this->updateUserMeta('stripe_person_token', $this->resp->id);
    }

    /**
     * getAccountId
     *
     * @return string
     */
    public function getAccountId(): string
    {
        if (!empty($this->stripeAccountId)) {
            return $this->stripeAccountId;
        }

        return $this->getUserMeta('stripe_account_id');
    }

    /**
     * getAccountType
     *
     * @return string
     */
    public function getAccountType(): string
    {
        if (!empty($this->stripeAccountType)) {
            return $this->stripeAccountType;
        }

        $this->stripeAccountType = $this->getUserMeta('stripe_account_type');
        if (empty($this->stripeAccountType)) {
            $this->accessAccountType();
            $this->updateUserMeta('stripe_account_type', $this->stripeAccountType);
        }
        return $this->stripeAccountType;
    }

    /**
     * accessAccountType
     *
     * @return bool
     */
    private function accessAccountType(): bool
    {
        if (false === $this->loadRemoteUserInfo()) {
            return false;
        }
        $this->userInfoObj = $this->getResponse();
        $this->stripeAccountType = $this->userInfoObj->type;
        return true;
    }

    /**
     * getRelationshipPersonId
     *
     * @return string
     */
    public function getRelationshipPersonId(): string
    {
        return (string) $this->getUserMeta('stripe_person_id');
    }

    /**
     * loadRemoteUserInfo
     *
     * @return bool
     */
    public function loadRemoteUserInfo(): bool
    {
        if (!empty($this->userInfoObj)) {
            $this->resp = $this->userInfoObj;
            return true;
        }

        $this->resp = $this->doRequest(self::REQUEST_RETRIEVE_ACCOUNT);
        if (false === $this->resp) {
            return false;
        }
        return true;
    }

    /**
     * getBusinessTypeFields
     *
     * @param  string $businessType
     * @return array
     */
    public function getBusinessTypeFields(string $businessType): array
    {
        $businessType = 'individual' == $businessType ? $businessType : 'other';

        $commonPreFields = [
            'business_type' => [
                'title' => Labels::getLabel("MSG_BUSINESS_TYPE", $this->langId),
                'description' => '',
                'required' => true
            ],
            'business_profile.url' => [
                'title' => Labels::getLabel("MSG_URL", $this->langId),
                'description' => Labels::getLabel('API_THE_BUSINESS_PUBLICLY_AVAILABLE_WEBSITE', $this->langId),
                'required' => true
            ],
            'business_profile.support_url' => [
                'title' => Labels::getLabel("MSG_SUPPORT_URL", $this->langId),
                'description' => Labels::getLabel('API_A_PUBLICLY_AVAILABLE_WEBSITE_FOR_HANDLING_SUPPORT_ISSUES', $this->langId),
                'required' => true
            ],
            'business_profile.name' => [
                'title' => Labels::getLabel("MSG_BUSINESS_PROFILE_NAME", $this->langId),
                'description' => Labels::getLabel('API_THE_CUSTOMER_FACING_BUSINESS_NAME', $this->langId),
                'required' => true
            ],
            'business_profile.support_phone' => [
                'title' => Labels::getLabel("MSG_SUPPORT_PHONE", $this->langId),
                'description' => Labels::getLabel('API_A_PUBLICLY_AVAILABLE_PHONE_NUMBER_TO_CALL_WITH_SUPPORT_ISSUES', $this->langId),
                'required' => true
            ],
            'business_profile.support_email' => [
                'title' => Labels::getLabel("MSG_SUPPORT_EMAIL", $this->langId),
                'description' => Labels::getLabel('API_A_PUBLICLY_AVAILABLE_EMAIL_ADDRESS_FOR_SENDING_SUPPORT_ISSUES_TO', $this->langId),
                'required' => true
            ],
            'business_profile.support_address.line1' => [
                'title' => Labels::getLabel("MSG_SUPPORT_ADDRESS_LINE_1", $this->langId),
                'description' => Labels::getLabel('API_ADDRESS_LINE_1', $this->langId),
                'required' => true
            ],
            'business_profile.support_address.line2' => [
                'title' => Labels::getLabel("MSG_SUPPORT_ADDRESS_LINE_2", $this->langId),
                'description' => Labels::getLabel('API_ADDRESS_LINE_2', $this->langId),
                'required' => true
            ],
            'business_profile.support_address.postal_code' => [
                'title' => Labels::getLabel("MSG_SUPPORT_ADDRESS_POSTAL_CODE", $this->langId),
                'description' => Labels::getLabel('API_ZIP_OR_POSTAL_CODE', $this->langId),
                'required' => true
            ],
            'business_profile.support_address.city' => [
                'title' => Labels::getLabel("MSG_SUPPORT_ADDRESS_CITY", $this->langId),
                'description' => Labels::getLabel('API_CITY_DISTRICT_SUBURB_TOWN_OR_VILLAGE', $this->langId),
                'required' => true
            ],
            'business_profile.support_address.country' => [
                'title' => Labels::getLabel("MSG_SUPPORT_ADDRESS_COUNTRY", $this->langId),
                'description' => Labels::getLabel('API_TWO_LETTER_COUNTRY_CODE', $this->langId),
                'required' => true
            ],
            'business_profile.support_address.state' => [
                'title' => Labels::getLabel("MSG_SUPPORT_ADDRESS_STATE", $this->langId),
                'description' => Labels::getLabel('API_STATE_COUNTY_PROVINCE_OR_REGION', $this->langId),
                'required' => true
            ],
        ];

        $bussinessTypeFileds = [
            'individual' => [
                'individual.id_number' => [
                    'title' => Labels::getLabel("MSG_ID_NUMBER", $this->langId),
                    'description' => Labels::getLabel('API_THE_GOVERNMENT_ISSUED_ID_NUMBER', $this->langId),
                    'required' => false
                ],
                'individual.first_name' => [
                    'title' => Labels::getLabel("MSG_FIRST_NAME", $this->langId),
                    'description' => Labels::getLabel('API_THE_INDIVIDUAL_FIRST_NAME', $this->langId),
                    'required' => true
                ],
                'individual.last_name' => [
                    'title' => Labels::getLabel("MSG_LAST_NAME", $this->langId),
                    'description' => Labels::getLabel('API_THE_INDIVIDUAL_LAST_NAME', $this->langId),
                    'required' => true
                ],
                'individual.email' => [
                    'title' => Labels::getLabel("MSG_EMAIL", $this->langId),
                    'description' => Labels::getLabel('API_THE_INDIVIDUAL_EMAIL_ADDRESS', $this->langId),
                    'required' => false
                ],
                'individual.phone' => [
                    'title' => Labels::getLabel("MSG_PHONE", $this->langId),
                    'description' => Labels::getLabel('API_THE_INDIVIDUAL_PHONE_NUMBER', $this->langId),
                    'required' => true
                ],
                'individual.dob.month' => [
                    'title' => Labels::getLabel("MSG_BIRTH_MONTH", $this->langId),
                    'description' => Labels::getLabel('API_THE_MONTH_OF_BIRTH_BETWEEN_1_AND_12', $this->langId),
                    'required' => true
                ],
                'individual.dob.day' => [
                    'title' => Labels::getLabel("MSG_BIRTH_DAY", $this->langId),
                    'description' => Labels::getLabel('API_THE_DAY_OF_BIRTH_BETWEEN_1_AND_31', $this->langId),
                    'required' => true
                ],
                'individual.dob.year' => [
                    'title' => Labels::getLabel("MSG_BIRTH_YEAR", $this->langId),
                    'description' => Labels::getLabel('API_THE_FOUR_DIGIT_YEAR_OF_BIRTH', $this->langId),
                    'required' => true
                ],
                'individual.address.line1' => [
                    'title' => Labels::getLabel("MSG_ADDRESS_LINE1", $this->langId),
                    'description' => Labels::getLabel('API_ADDRESS_LINE_1', $this->langId),
                    'required' => true
                ],
                'individual.address.city' => [
                    'title' => Labels::getLabel("MSG_CITY", $this->langId),
                    'description' => Labels::getLabel('API_CITY_DISTRICT_SUBURB_TOWN_OR_VILLAGE', $this->langId),
                    'required' => true
                ],
                'individual.address.postal_code' => [
                    'title' => Labels::getLabel("MSG_POSTAL_CODE", $this->langId),
                    'description' => Labels::getLabel('API_ZIP_OR_POSTAL_CODE', $this->langId),
                    'required' => true
                ],
                'individual.address.country' => [
                    'title' => Labels::getLabel("MSG_COUNTRY", $this->langId),
                    'description' => Labels::getLabel('API_TWO_LETTER_COUNTRY_CODE', $this->langId),
                    'required' => false
                ],
                'individual.address.state' => [
                    'title' => Labels::getLabel("MSG_STATE", $this->langId),
                    'description' => Labels::getLabel('API_STATE_COUNTY_PROVINCE_OR_REGION', $this->langId),
                    'required' => false
                ],
                'individual.verification.document' => [
                    'title' => Labels::getLabel("MSG_DOCUMENT", $this->langId),
                    'description' => '',
                    'required' => false
                ],
            ],
            'other' => [
                'company.address.line1' => [
                    'title' => Labels::getLabel("MSG_ADDRESS_LINE1", $this->langId),
                    'description' => Labels::getLabel('API_ADDRESS_LINE_1', $this->langId),
                    'required' => true
                ],
                'company.address.city' => [
                    'title' => Labels::getLabel("MSG_CITY", $this->langId),
                    'description' => Labels::getLabel('API_CITY_DISTRICT_SUBURB_TOWN_OR_VILLAGE', $this->langId),
                    'required' => true
                ],
                'company.address.postal_code' => [
                    'title' => Labels::getLabel("MSG_POSTAL_CODE", $this->langId),
                    'description' => Labels::getLabel('API_ZIP_OR_POSTAL_CODE', $this->langId),
                    'required' => true
                ],
                'company.address.country' => [
                    'title' => Labels::getLabel("MSG_COUNTRY", $this->langId),
                    'description' => Labels::getLabel('API_TWO_LETTER_COUNTRY_CODE', $this->langId),
                    'required' => true
                ],
                'company.address.state' => [
                    'title' => Labels::getLabel("MSG_STATE", $this->langId),
                    'description' => Labels::getLabel('API_STATE_COUNTY_PROVINCE_OR_REGION', $this->langId),
                    'required' => true
                ],
                'company.name' => [
                    'title' => Labels::getLabel("MSG_NAME", $this->langId),
                    'description' => Labels::getLabel('API_THE_COMPANY_LEGAL_NAME', $this->langId),
                    'required' => true
                ],
                'company.phone' => [
                    'title' => Labels::getLabel("MSG_PHONE", $this->langId),
                    'description' => Labels::getLabel('API_THE_COMPANY_PHONE_NUMBER', $this->langId),
                    'required' => true
                ],
                'company.tax_id' => [
                    'title' => Labels::getLabel("MSG_TAX_ID", $this->langId),
                    'description' => Labels::getLabel('API_THE_BUSINESS_ID_NUMBER', $this->langId),
                    'required' => true
                ],
                'relationship_person.address.line1' => [
                    'title' => Labels::getLabel("MSG_RELATIONSHIP_PERSON_ADDRESS_LINE1", $this->langId),
                    'description' => Labels::getLabel('API_ADDRESS_LINE_1', $this->langId),
                    'required' => false
                ],
                'relationship_person.address.city' => [
                    'title' => Labels::getLabel("MSG_RELATIONSHIP_PERSON_CITY", $this->langId),
                    'description' => Labels::getLabel('API_CITY_DISTRICT_SUBURB_TOWN_OR_VILLAGE', $this->langId),
                    'required' => false
                ],
                'relationship_person.address.postal_code' => [
                    'title' => Labels::getLabel("MSG_RELATIONSHIP_PERSON_POSTAL_CODE", $this->langId),
                    'description' => Labels::getLabel('API_ZIP_OR_POSTAL_CODE', $this->langId),
                    'required' => false
                ],
                'relationship_person.address.country' => [
                    'title' => Labels::getLabel("MSG_RELATIONSHIP_PERSON_COUNTRY", $this->langId),
                    'description' => Labels::getLabel('API_TWO_LETTER_COUNTRY_CODE', $this->langId),
                    'required' => false
                ],
                'relationship_person.address.state' => [
                    'title' => Labels::getLabel("MSG_RELATIONSHIP_PERSON_STATE", $this->langId),
                    'description' => Labels::getLabel('API_STATE_COUNTY_PROVINCE_OR_REGION', $this->langId),
                    'required' => false
                ],
                'relationship_person.dob.month' => [
                    'title' => Labels::getLabel("MSG_RELATIONSHIP_PERSON_BIRTH_MONTH", $this->langId),
                    'description' => Labels::getLabel('API_THE_MONTH_OF_BIRTH_BETWEEN_1_AND_12', $this->langId),
                    'required' => true
                ],
                'relationship_person.dob.day' => [
                    'title' => Labels::getLabel("MSG_RELATIONSHIP_PERSON_BIRTH_DAY", $this->langId),
                    'description' => Labels::getLabel('API_THE_DAY_OF_BIRTH_BETWEEN_1_AND_31', $this->langId),
                    'required' => true
                ],
                'relationship_person.dob.year' => [
                    'title' => Labels::getLabel("MSG_RELATIONSHIP_PERSON_BIRTH_YEAR", $this->langId),
                    'description' => Labels::getLabel('API_THE_FOUR_DIGIT_YEAR_OF_BIRTH', $this->langId),
                    'required' => true
                ],
                'relationship_person.email' => [
                    'title' => Labels::getLabel("MSG_RELATIONSHIP_PERSON_EMAIL", $this->langId),
                    'description' => Labels::getLabel('API_THE_RELATIONSHIP_PERSON_EMAIL_ADDRESS', $this->langId),
                    'required' => false
                ],
                'relationship_person.first_name' => [
                    'title' => Labels::getLabel("MSG_RELATIONSHIP_PERSON_FIRST_NAME", $this->langId),
                    'description' => Labels::getLabel('API_THE_RELATIONSHIP_PERSON_FIRST_NAME', $this->langId),
                    'required' => true
                ],
                'relationship_person.last_name' => [
                    'title' => Labels::getLabel("MSG_RELATIONSHIP_PERSON_LAST_NAME", $this->langId),
                    'description' => Labels::getLabel('API_THE_RELATIONSHIP_PERSON_LAST_NAME', $this->langId),
                    'required' => true
                ],
                'relationship_person.phone' => [
                    'title' => Labels::getLabel("MSG_RELATIONSHIP_PERSON_PHONE", $this->langId),
                    'description' => Labels::getLabel('API_THE_RELATIONSHIP_PERSON_PHONE_NUMBER', $this->langId),
                    'required' => true
                ],
                'relationship_person.ssn_last_4' => [
                    'title' => Labels::getLabel("MSG_RELATIONSHIP_PERSON_SSN_LAST_4", $this->langId),
                    'description' => Labels::getLabel('API_THE_RELATIONSHIP_PERSON_SOCIAL_SECURITY_NUMBER', $this->langId),
                    'required' => false
                ],
                'relationship.title' => [
                    'title' => Labels::getLabel("MSG_TITLE", $this->langId),
                    'description' => Labels::getLabel('API_THE_PERSON_TITLE', $this->langId),
                    'required' => true
                ],
                'relationship.owner' => [
                    'title' => Labels::getLabel("MSG_OWNER", $this->langId),
                    'description' => Labels::getLabel('API_WHETHER_THE_PERSON_IS_AN_OWNER_OF_THE_ACCOUNT_LEGAL_ENTITY', $this->langId),
                    'required' => true
                ],
                'relationship.director' => [
                    'title' => Labels::getLabel("MSG_DIRECTOR", $this->langId),
                    'description' => Labels::getLabel("API_WHETHER_THE_PERSON_IS_A_DIRECTOR_OF_THE_LEGAL_ENTITY", $this->langId),
                    'required' => true
                ],
                'relationship.executive' => [
                    'title' => Labels::getLabel("MSG_EXECUTIVE", $this->langId),
                    'description' => Labels::getLabel("API_WHETHER_THE_PERSON_IS_AN_EXECUTIVE_OF_THE_LEGAL_ENTITY", $this->langId),
                    'required' => true
                ],
            ]
        ];

        $commonPostFields = [
            'business_profile.mcc' => [
                'title' => Labels::getLabel("MSG_MERCHANT_CATEGORY_CODE", $this->langId),
                'description' => Labels::getLabel('API_THE_MERCHANT_CATEGORY_CODE', $this->langId),
                'required' => true
            ],
            'external_account.account_holder_name' => [
                'title' => Labels::getLabel("MSG_BANK_ACCOUNT_HOLDER_NAME", $this->langId),
                'description' => Labels::getLabel('API_THE_NAME_OF_THE_PERSON_OR_BUSINESS_THAT_OWNS_THE_BANK_ACCOUNT', $this->langId),
                'required' => true
            ],
            'external_account.account_number' => [
                'title' => Labels::getLabel("MSG_BANK_ACCOUNT_NUMBER", $this->langId),
                'description' => Labels::getLabel('API_THE_BANK_ACCOUNT_NUMBER', $this->langId),
                'required' => true
            ],
            'external_account.routing_number' => [
                'title' => Labels::getLabel("MSG_BANK_ROUTING_NUMBER", $this->langId),
                'description' => Labels::getLabel('API_THE_ROUTING_NUMBER', $this->langId),
                'required' => true
            ],
            'tos_acceptance' => [
                'title' => Labels::getLabel("LBL_I_AGREE_TO_THE_TERMS_OF_SERVICE", $this->langId),
                'description' => '',
                'required' => true
            ],
        ];

        return array_merge($commonPreFields, $bussinessTypeFileds[$businessType], $commonPostFields);
    }

    /**
     * getRequiredFields
     *
     * @param  string $businessType
     * @return array
     */
    public function getRequiredFields(string $businessType = 'individual'): array
    {
        if (empty($this->getAccountId()) || false === $this->loadRemoteUserInfo()) {
            return [];
        }
        $formSubmittedFlag = $this->getUserMeta('stripe_form_submitted');

        if (!empty($formSubmittedFlag)) {
            $this->userInfoObj = $this->getResponse();
            $currentlyDue = $this->userInfoObj->requirements->currently_due;
            $arr = [];
            array_walk($currentlyDue, function ($value, $key) use (&$arr) {
                $label = $value;
                if (false !== strpos($value, ".")) {
                    $label = str_replace(".", " ", $value);
                }

                if (false !== strpos($label, 'person_')) {
                    $personId = $this->getUserMeta('stripe_person_id');
                    $label = str_replace($personId, "Person", $label);
                }

                $arr[$value] = ucwords($label);
            });
            $this->requiredFields = $arr;
        } else {
            $this->requiredFields = $this->getBusinessTypeFields($businessType);
        }

        return $this->requiredFields;
    }
    
    /**
     * cleanRequest
     *
     * @param  array $array
     * @return array
     */
    private function cleanRequest(array $array): array
    {
        foreach ($array as $key => &$value) {
            if ('' == $value) {
                unset($array[$key]);
            } else {
                if (is_array($value)) {
                    $value = $this->cleanRequest($value);
                    if ('' == $value) {
                        unset($array[$key]);
                    }
                }
            }
        }
        return $array;
    }

    /**
     * updateRequiredFields
     *
     * @param array $requestParam
     * @param int $updateSubmittedFormFlag
     * @return bool
     */
    public function updateRequiredFields(array $requestParam): bool
    {
        if (array_key_exists('external_account', $requestParam)) {
            $this->loadBaseCurrencyCode();
            $requestParam['external_account']['object'] = 'bank_account';
            $requestParam['external_account']['country'] = strtoupper($this->userData['country_code']);
            $requestParam['external_account']['currency'] = $this->systemCurrencyCode;
        }
        
        $requestParam = $this->cleanRequest($requestParam);

        if (array_key_exists('relationship', $requestParam)) {
            $requestParam['company']['directors_provided'] = true;
            $requestParam['company']['executives_provided'] = true;
            $requestParam['company']['owners_provided'] = true;
            $requestParam['relationship']['representative'] = true;
        }

        return $this->doRequest(self::REQUEST_UPDATE_ACCOUNT, $requestParam);
    }

    /**
     * addFinancialInfo
     *
     * @param array $requestParam
     * @return bool
     */
    private function addFinancialInfo(array $requestParam): bool
    {
        $businessType = $this->getUserMeta('stripe_business_type');

        $this->loadBaseCurrencyCode();
        $requestParam = [
            'external_account' => [
                'object' => 'bank_account',
                'account_holder_name' => $requestParam['account_holder_name'],
                'account_number' => $requestParam['account_number'],
                'account_holder_type' => $businessType,
                'country' => strtoupper($this->userData['country_code']),
                'currency' => $this->systemCurrencyCode,
                'routing_number' => $requestParam['routing_number'],
            ]
        ];

        $this->resp = $this->createExternalAccount($requestParam);
        if (false === $this->resp) {
            return false;
        }
        return $this->updateUserMeta('stripe_bank_account_id', $this->resp->id);
    }

    /**
     * updateAccount
     *
     * @param array $requestParam
     * @return bool
     */
    private function updateAccount(array $requestParam): bool
    {
        $relationship = [];
        $person = [];
        $personData = [];

        $personId = $this->getRelationshipPersonId();

        $person = isset($requestParam['relationship_person']) ? $requestParam['relationship_person'] : [];
        if (!empty($person)) {
            unset($requestParam['relationship_person']);
        }

        if (array_key_exists('relationship', $requestParam)) {
            $relationship = $requestParam['relationship'];
            unset($requestParam['relationship']);
        }
        
        if (!empty($personId) && array_key_exists($personId, $requestParam)) {
            $personData = $requestParam[$personId];
            unset($requestParam[$personId]);
        }

        if (array_key_exists('tos_acceptance', $requestParam)) {
            $requestParam['tos_acceptance'] =  [
                'date' => time(),
                'ip' => CommonHelper::getClientIp(),
                'user_agent' => CommonHelper::userAgent(),
            ];
        }

        if (!empty($requestParam) && false === $this->update($requestParam)) {
            return false;
        }

        if (array_key_exists('business_type', $requestParam)) {
            $this->updateUserMeta('stripe_business_type', $requestParam['business_type']);
        }

        if (!empty($requestParam)) {
            $this->resp = $this->update($requestParam);
        }

        if (empty($personId) && !empty($relationship)) {
            $personDataToUpdate = array_merge($person, ['relationship' => $relationship]);
            $this->resp = $this->doRequest(self::REQUEST_CREATE_PERSON, $personDataToUpdate);
            if (false === $this->resp) {
                return false;
            }
            $this->updateUserMeta('stripe_person_id', $this->resp->id);
        } elseif (!empty($personId) && (!empty($relationship) || !empty($personData))) {
            $relationship = !empty($relationship) ? ['relationship' => $relationship] : [];
            $requestParam = array_merge($relationship, $personData);
            $this->resp = $this->doRequest(self::REQUEST_UPDATE_PERSON, $requestParam);
            if (false === $this->resp) {
                return false;
            }
        }

        $formSubmittedFlag = $this->getUserMeta('stripe_form_submitted');
        if (empty($formSubmittedFlag)) {
            $this->updateUserMeta('stripe_form_submitted', 1);
        }

        return true;
    }

    /**
     * uploadVerificationFile
     *
     * @param string $path
     * @return bool
     */
    public function uploadVerificationFile(string $path): bool
    {
        $this->resp = $this->doRequest(self::REQUEST_UPLOAD_VERIFICATION_FILE, [$path]);
        if (false === $this->resp) {
            return false;
        }
        return true;
    }

    /**
     * updateVericationDocument
     *
     * @param string $side - Front/Back of uploaded document
     * @return bool
     */
    public function updateVericationDocument(string $side): bool
    {
        if (empty($this->resp)) {
            $this->error = Labels::getLabel('LBL_INVALID_REQUEST', $this->langId);
            return false;
        }

        $requestParam = [
            'verification' => [
                'document' => [
                    $side => $this->resp
                ]
            ]
        ];
        $this->resp = $this->doRequest(self::REQUEST_UPDATE_PERSON, $requestParam);
        return (false === $this->resp) ? false : true;
    }

    /**
     * getErrorWhileUpdate
     *
     * @return array
     */
    public function getErrorWhileUpdate(): array
    {
        return (false === $this->loadRemoteUserInfo()) ? [] : ($this->getResponse()->toArray())['requirements']['errors'];
    }

    /**
     * deleteAccount
     *
     * @return bool
     */
    public function deleteAccount(): bool
    {
        $this->resp = $this->doRequest(self::REQUEST_DELETE_ACCOUNT);

        if (false === $this->resp) {
            return false;
        }

        $this->resp = $this->resp->toArray();
        if (array_key_exists('deleted', $this->resp) && true == $this->resp['deleted']) {
            $this->unsetUserAccountElements();
            return true;
        }

        $this->error = Labels::getLabel('MSG_UNABLE_TO_DELETE_THIS_ACCOUNT', $this->langId);
        return false;
    }

    /**
     * initiateSession
     *
     * @param array $requestParam
     * @return bool
     */
    public function initiateSession(array $requestParam): bool
    {
        if (empty($requestParam)) {
            $this->error = Labels::getLabel('MSG_INVALID_REQUEST', $this->langId);
            return false;
        }

        $this->resp = $this->doRequest(self::REQUEST_CREATE_SESSION, $requestParam);
        if (false === $this->resp) {
            return false;
        }
        $this->sessionId = $this->resp->id;
        return true;
    }

    /**
     * getSessionId
     *
     * @return string
     */
    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    /**
     * createPriceObject
     *
     * @param array $requestParam
     * @return bool
     */
    public function createPriceObject(array $requestParam): bool
    {
        if (empty($requestParam)) {
            $this->error = Labels::getLabel('MSG_INVALID_REQUEST', $this->langId);
            return false;
        }

        $this->resp = $this->doRequest(self::REQUEST_CREATE_PRICE, $requestParam);
        if (false === $this->resp) {
            return false;
        }
        $this->priceId = $this->resp->id;
        return true;
    }

    /**
     * getPriceId
     *
     * @return string
     */
    public function getPriceId(): string
    {
        return $this->priceId;
    }

    /**
     * updateCustomerInfo
     *
     * @param array $requestParam
     * @return bool
     */
    public function updateCustomerInfo(array $requestParam): bool
    {
        $this->resp = $this->doRequest(self::REQUEST_UPDATE_CUSTOMER, $requestParam);
        return (false === $this->resp) ? false : true;
    }

    /**
     * bindCustomer
     *
     * @param array $requestParam
     * @return bool
     */
    public function bindCustomer(array $requestParam): bool
    {
        if (empty($requestParam)) {
            $this->error = Labels::getLabel('MSG_INVALID_REQUEST', $this->langId);
            return false;
        }

        if (!empty($this->getCustomerId())) {
            $this->resp = $this->updateCustomerInfo($requestParam);
        } else {
            $this->resp = $this->doRequest(self::REQUEST_CREATE_CUSTOMER, $requestParam);
        }

        if (false === $this->resp) {
            return false;
        }

        $this->customerId = empty($this->getCustomerId()) ? $this->resp->id : $this->getCustomerId();

        return $this->updateUserMeta('stripe_customer_id', $this->customerId);
    }

    /**
     * loadCustomer
     *
     * @return bool
     */
    public function loadCustomer(): bool
    {
        if (!empty($this->customerInfo)) {
            $this->resp = $this->customerInfo;
            return true;
        }

        $this->resp = $this->customerInfo = $this->doRequest(self::REQUEST_RETRIEVE_CUSTOMER);
        return (false === $this->resp) ? false : true;
    }

    /**
     * getCustomerId
     *
     * @return string
     */
    public function getCustomerId(): string
    {
        return empty($this->customerId) ? $this->getUserMeta('stripe_customer_id') : $this->customerId;
    }

    /**
     * formatCustomerDataFromOrder
     * @param array $orderInfo
     * @return array
     */
    public function formatCustomerDataFromOrder(array $orderInfo): array
    {
        if (empty($orderInfo)) {
            return [];
        }

        $orderData = [
            'address' => [
                'line1' => $orderInfo['customer_billing_address_1'],
                'line2' => $orderInfo['customer_billing_address_2'],
                'city' => $orderInfo['customer_billing_city'],
                'state' => $orderInfo['customer_billing_state'],
                'country' => $orderInfo['customer_billing_country'],
                'postal_code' => $orderInfo['customer_billing_postcode']
            ],
            'shipping' => [
                'address' => [
                    'line1' => $orderInfo['customer_shipping_address_1'],
                    'line2' => $orderInfo['customer_shipping_address_2'],
                    'city' => $orderInfo['customer_shipping_city'],
                    'state' => $orderInfo['customer_shipping_state'],
                    'country' => $orderInfo['customer_shipping_country'],
                    'postal_code' => $orderInfo['customer_shipping_postcode']
                ],
                'name' => $orderInfo['customer_shipping_name'],
                'phone' => $orderInfo['customer_shipping_phone']
            ],
            'email' => $orderInfo['customer_email'],
            'name' => $orderInfo['customer_billing_name'],
            'phone' => $orderInfo['customer_billing_phone']
        ];
        return $orderData;
    }

    /**
     * createLoginLink
     *
     * @return bool
     */
    public function createLoginLink(): bool
    {
        $this->resp = $this->doRequest(self::REQUEST_CREATE_LOGIN_LINK);
        if (false === $this->resp) {
            return false;
        }
        $this->loginUrl = $this->resp->url;
        return true;
    }

    /**
     * getLoginUrl
     *
     * @return string
     */
    public function getLoginUrl(): string
    {
        return $this->loginUrl;
    }

    /**
     * loadAllAccounts
     *
     * @param $requestParam - Used for pagination
     * Detail : https://stripe.com/docs/api/accounts/list?lang=php
     * @return bool
     */
    public function loadAllAccounts(array $requestParam = ['limit' => 10]): bool
    {
        $this->resp = $this->doRequest(self::REQUEST_ALL_CONNECT_ACCOUNTS, $requestParam);
        if (false === $this->resp) {
            return false;
        }
        $this->connectedAccounts = $this->resp->toArray();
        return true;
    }

    /**
     * getAllAccounts
     *
     * @return array
     */
    public function getAllAccounts(): array
    {
        return $this->connectedAccounts;
    }

    /**
     * initiateRefund
     *
     * @param $requestParam
     * Follow : https://stripe.com/docs/api/refunds/create
     * @return bool
     */
    public function initiateRefund(array $requestParam): bool
    {
        $this->resp = $this->doRequest(self::REQUEST_INITIATE_REFUND, $requestParam);
        if (false === $this->resp) {
            return false;
        }
        return true;
    }

    /**
     * doTransfer
     *
     * @param array $requestParam : [
     *         'amount' => 7000,
     *         'currency' => 'inr',
     *         'destination' => '{{CONNECTED_STRIPE_ACCOUNT_ID}}',
     *         'transfer_group' => '{ORDER10}',
     *       ]
     * @return bool
     */
    public function doTransfer(array $requestParam): bool
    {
        $this->resp = $this->doRequest(self::REQUEST_TRANSFER_AMOUNT, $requestParam);
        if (false === $this->resp) {
            return false;
        }
        return true;
    }

    /**
     * revertTransfer
     *
     * @param array $requestParam : [
     *         'transferId' => 'tr_1XXXXXXXXXXXX,
     *         'data' => [
     *              'amount' => 1000, // In Paisa
     *              'description' => '',
     *              'metadata' => [
     *                  'xyz' => 'abc' // Set of key-value pairs that you can attach to an object.
     *              ],
     *          ],
     *       ]
     * @return bool
     */
    public function revertTransfer(array $requestParam): bool
    {
        $this->resp = $this->doRequest(self::REQUEST_REVERSE_TRANSFER, $requestParam);
        if (false === $this->resp) {
            return false;
        }
        return true;
    }

    /**
     * addCard
     *
     * @return bool
     */
    public function addCard(array $requestParam): bool
    {
        $this->resp = $this->doRequest(self::REQUEST_ADD_CARD, $requestParam);
        if (false === $this->resp) {
            return false;
        }
        return true;
    }

    /**
     * removeCard
     *
     * @param array $requestParam : ['cardId' => 'card_xxxxx']
     * @return bool
     */
    public function removeCard(array $requestParam): bool
    {
        $this->resp = $this->doRequest(self::REQUEST_REMOVE_CARD, $requestParam);
        if (false === $this->resp) {
            return false;
        }
        return true;
    }

    /**
     * loadSavedCards - Retrieve All Saved Cards
     *
     * @return bool
     */
    public function loadSavedCards(): bool
    {
        $this->resp = $this->doRequest(self::REQUEST_LIST_ALL_CARDS);
        if (false === $this->resp) {
            return false;
        }
        return true;
    }

    /**
     * generateCardToken
     *
     * @param array $requestParam : [
     *           'number' => '4242424242424242',
     *           'exp_month' => 6,
     *           'exp_year' => 2021,
     *           'cvc' => '314',
     *       ]
     * @return bool
     */
    public function generateCardToken(array $requestParam): bool
    {
        $this->resp = $this->doRequest(self::REQUEST_CREATE_CARD_TOKEN, $requestParam);
        if (false === $this->resp) {
            return false;
        }
        return true;
    }

    /**
     * doCharge
     *
     * @param array $requestParam
     * @return bool
     */
    public function doCharge(array $requestParam): bool
    {
        $this->resp = $this->doRequest(self::REQUEST_CHARGE, $requestParam);
        if (false === $this->resp) {
            return false;
        }
        return true;
    }

    /**
     * createPaymentIntent
     *
     * @param array $requestParam
     * @return bool
     */
    public function createPaymentIntent(array $requestParam): bool
    {
        $this->resp = $this->doRequest(self::REQUEST_PAYMENT_INTENT, $requestParam);
        if (false === $this->resp) {
            return false;
        }
        return true;
    }

    /**
     * loadPaymentIntent
     *
     * @param string $paymentIntentId
     * @return bool
     */
    public function loadPaymentIntent(string $paymentIntentId): bool
    {
        $this->resp = $this->doRequest(self::REQUEST_RETRIEVE_PAYMENT_INTENT, [$paymentIntentId]);
        if (false === $this->resp) {
            return false;
        }
        return true;
    }

    /**
     * addPaymentMethod
     *
     * @param array $requestParam
     * @return bool
     */
    public function addPaymentMethod(array $requestParam): bool
    {
        $this->resp = $this->doRequest(self::REQUEST_CREATE_PAYMENT_METHOD, $requestParam);
        if (false === $this->resp) {
            return false;
        }
        return true;
    }

    /**
     * fetchCards
     *
     * @return bool
     */
    public function fetchCards(): bool
    {
        if (false === $this->loadCustomer()) {
            return false;
        }
        $customerInfo = $this->getResponse()->toArray();
        $this->resp = $customerInfo['sources']['data'];
        return true;
    }

    /**
     * getDefaultCard
     *
     * @return bool
     */
    public function getDefaultCard(): bool
    {
        if (false === $this->loadCustomer()) {
            return false;
        }
        $customerInfo = $this->getResponse()->toArray();
        $this->resp = $customerInfo['default_source'];
        return true;
    }

    /**
     * captureDetainedAmount
     * @param array $requestParam : [
     *      'paymentIntentId' => 'pi_JRXXXXXXXXXXXXX',
     *      'amount_to_capture' => 750,
     *      'statement_descriptor' => 'TEXT' // Description that appears on your customers’ statements. Length at least one letter, maximum 22 characters.
     *   ]
     * @return bool
     */
    public function captureDetainedAmount(array $requestParam): bool
    {
        $this->resp = $this->doRequest(self::REQUEST_CAPTURE_PAYMENT, $requestParam);
        if (false === $this->resp) {
            return false;
        }
        return true;
    }

    /**
     * getMerchantCategory
     *
     * @param  string $keyword
     * @param  bool $returnFullArray
     * @return array
     */
    public function getMerchantCategory(string $keyword = '', bool $returnFullArray = false): array
    {
        $arr = [];
        if ($fh = fopen(__DIR__ . '/MerchantCategoryCode.txt', 'r')) {
            $rowIndex = 1;
            while (!feof($fh)) {
                $line = fgets($fh);
                if ($returnFullArray || false !== stripos($line, $keyword)) {
                    $lineContentArr = explode('-', $line, 2);
                    if (!empty($lineContentArr) && 1 < count($lineContentArr)) {
                        $arr[trim($lineContentArr[0])] = trim($lineContentArr[1]);
                    }
                    $rowIndex++;
                }

                if (false === $returnFullArray && $rowIndex == self::PAGE_SIZE) {
                    break;
                }
            }
            fclose($fh);
        }
        ksort($arr);
        return $arr;
    }

    /**
     * doRequest
     *
     * @param  mixed $requestType
     * @return mixed
     */
    public function doRequest(int $requestType, array $requestParam = [])
    {
        try {
            switch ($requestType) {
                case self::REQUEST_CREATE_ACCOUNT:
                    return $this->createAccount();
                    break;
                case self::REQUEST_RETRIEVE_ACCOUNT:
                    return $this->retrieve();
                    break;
                case self::REQUEST_UPDATE_ACCOUNT:
                    return $this->updateAccount($requestParam);
                    break;
                case self::REQUEST_PERSON_TOKEN:
                    return $this->getPersonToken();
                    break;
                case self::REQUEST_ADD_BANK_ACCOUNT:
                    return $this->addFinancialInfo($requestParam);
                    break;
                case self::REQUEST_CREATE_PERSON:
                    return $this->createPerson($requestParam);
                    break;
                case self::REQUEST_UPDATE_PERSON:
                    return $this->updatePerson($requestParam);
                    break;
                case self::REQUEST_UPLOAD_VERIFICATION_FILE:
                    return $this->createFile(reset($requestParam));
                    break;
                case self::REQUEST_DELETE_ACCOUNT:
                    return $this->delete();
                    break;
                case self::REQUEST_CREATE_SESSION:
                    return $this->createSession($requestParam);
                    break;
                case self::REQUEST_CREATE_PRICE:
                    return $this->createPrice($requestParam);
                    break;
                case self::REQUEST_CREATE_CUSTOMER:
                    return $this->createCustomer($requestParam);
                    break;
                case self::REQUEST_RETRIEVE_CUSTOMER:
                    return $this->retrieveCustomer();
                    break;
                case self::REQUEST_UPDATE_CUSTOMER:
                    return $this->updateCustomer($requestParam);
                    break;
                case self::REQUEST_CREATE_LOGIN_LINK:
                    return $this->loginLink();
                    break;
                case self::REQUEST_ALL_CONNECT_ACCOUNTS:
                    return $this->connectedAccounts($requestParam);
                    break;
                case self::REQUEST_INITIATE_REFUND:
                    return $this->requestRefund($requestParam);
                    break;
                case self::REQUEST_TRANSFER_AMOUNT:
                    return $this->transferAmount($requestParam);
                    break;
                case self::REQUEST_REVERSE_TRANSFER:
                    return $this->reverseTransfer($requestParam);
                    break;
                case self::REQUEST_ADD_CARD:
                    return $this->createSource($requestParam);
                    break;
                case self::REQUEST_REMOVE_CARD:
                    return $this->deleteSource($requestParam);
                    break;
                case self::REQUEST_LIST_ALL_CARDS:
                    return $this->listAllCards();
                    break;
                case self::REQUEST_CREATE_CARD_TOKEN:
                    return $this->createCardToken($requestParam);
                    break;
                case self::REQUEST_CHARGE:
                    return $this->charge($requestParam);
                    break;
                case self::REQUEST_PAYMENT_INTENT:
                    return $this->paymentIntent($requestParam);
                    break;
                case self::REQUEST_RETRIEVE_PAYMENT_INTENT:
                    return $this->retrievePaymentIntent($requestParam);
                    break;
                case self::REQUEST_CREATE_PAYMENT_METHOD:
                    return $this->createPaymentMethod($requestParam);
                    break;
                case self::REQUEST_CAPTURE_PAYMENT:
                    return $this->capturePayment($requestParam);
                    break;
            }
        } catch (\Stripe\Exception\CardException $e) {
            // Since it's a decline, \Stripe\Exception\CardException will be caught
            $this->error = $e->getMessage();
        } catch (\Stripe\Exception\RateLimitException $e) {
            // Too many requests made to the API too quickly
            $this->error = $e->getMessage();
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            // Invalid parameters were supplied to Stripe's API
            $this->error = $e->getMessage();
        } catch (\Stripe\Exception\AuthenticationException $e) {
            // Authentication with Stripe's API failed
            $this->error = $e->getMessage();
            // (maybe you changed API keys recently)
        } catch (\Stripe\Exception\ApiConnectionException $e) {
            // Network communication with Stripe failed
            $this->error = $e->getMessage();
        } catch (\Stripe\Exception\ApiErrorException $e) {
            // Display a very generic error to the user, and maybe send
            $this->error = $e->getMessage();
            // yourself an email
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Display a very generic error to the user, and maybe send
            $this->error = $e->getMessage();
            // yourself an email
        } catch (\UnexpectedValueException $e) {
            // Display a very generic error to the user, and maybe send
            $this->error = $e->getMessage();
            // yourself an email
        } catch (Exception $e) {
            // Something else happened, completely unrelated to Stripe
            $this->error = $e->getMessage();
        } catch (Error $e) {
            // Handle error
            $this->error = $e->getMessage();
        }
        return false;
    }
}
