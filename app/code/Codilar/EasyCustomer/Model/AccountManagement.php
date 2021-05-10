<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codilar\EasyCustomer\Model;


use Codilar\EasyCustomer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Model\AccountConfirmation;
use Magento\Customer\Model\Customer\CredentialsValidator;
use Magento\Customer\Model\EmailNotificationInterface;
use Magento\Directory\Model\AllowedCountries;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Encryption\EncryptorInterface as Encryptor;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\Math\Random;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\StringUtils as StringHelper;
use Magento\Customer\Model\Config\Share as ConfigShare;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface as PsrLogger;
use Magento\Customer\Model\CustomerRegistry;

class AccountManagement implements AccountManagementInterface
{
    const MAX_PASSWORD_LENGTH = 256;//最大密码长度
    /**
     * Configuration path to customer password minimum length
     */
    const XML_PATH_MINIMUM_PASSWORD_LENGTH = 'customer/password/minimum_password_length';

    /**
     * Configuration path to customer password required character classes number
     */
    const XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER = 'customer/password/required_character_classes_number';

    /**
     * Constants for the type of new account email to be sent
     *
     * @deprecated get rid of Helpers in Password Security Management.
     * @see \Magento\Customer\Model\EmailNotificationInterface::NEW_ACCOUNT_EMAIL_REGISTERED
     */
    const NEW_ACCOUNT_EMAIL_REGISTERED = 'registered';

    /**
     * Welcome email, when confirmation is enabled
     *
     * @deprecated get rid of Helpers in Password Security Management.
     * @see \Magento\Customer\Model\EmailNotificationInterface::NEW_ACCOUNT_EMAIL_CONFIRMATION
     */
    const NEW_ACCOUNT_EMAIL_CONFIRMATION = 'confirmation';

    /**
     * Welcome email, when password setting is required
     *
     * @deprecated get rid of Helpers in Password Security Management.
     * @see \Magento\Customer\Model\EmailNotificationInterface::NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD
     */
    const NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD = 'registered_no_password';

    /**
     * @var StringHelper
     */
    private $stringHelper;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var CredentialsValidator
     */
    private $credentialsValidator;
    /**
     * @var Encryptor
     */
    private $encryptor;
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;
    /**
     * @var ConfigShare
     */
    private $configShare;
    /**
     * @var StoreManagerInterface
     */
    private  $storeManager;

    /**
     * @var AllowedCountries
     */
    private $allowedCountriesReader;
    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var Random
     */
    private Random $mathRandom;

    /**
     * @var AccountConfirmation
     */
    private $accountConfirmation;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @var EmailNotificationInterface
     */
    private $emailNotification;
    /**
     * @var PsrLogger
     */
    protected $logger;

    public function __construct(
        StringHelper $stringHelper,
        ScopeConfigInterface $scopeConfig,
        Encryptor $encryptor,
        CustomerRepositoryInterface $customerRepository,
        ConfigShare $configShare,
        StoreManagerInterface $storeManager,
        AddressRepositoryInterface $addressRepository,
        Random $mathRandom,
        PsrLogger $logger,
        CustomerRegistry $customerRegistry,
        AllowedCountries $allowedCountriesReader = null,
        DateTimeFactory $dateTimeFactory = null,
        AccountConfirmation $accountConfirmation = null,
        CredentialsValidator $credentialsValidator = null

    ) {
        $this->stringHelper = $stringHelper;
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
        $this->customerRepository = $customerRepository;
        $this->configShare = $configShare;
        $this->storeManager = $storeManager;
        $this->addressRepository = $addressRepository;
        $this->customerRegistry = $customerRegistry;
        $this->mathRandom = $mathRandom;
        $this->logger = $logger;
        $objectManager = ObjectManager::getInstance();
        $this->credentialsValidator = $credentialsValidator ?: $objectManager->get(CredentialsValidator::class);
        $this->allowedCountriesReader = $allowedCountriesReader
            ?: $objectManager->get(AllowedCountries::class);
        $this->dateTimeFactory = $dateTimeFactory ?: $objectManager->get(DateTimeFactory::class);
        $this->accountConfirmation = $accountConfirmation ?: $objectManager
            ->get(AccountConfirmation::class);

    }

    /**
     * @inheritdoc
     *
     * @throws LocalizedException
     */
    public function createAccount(CustomerInterface $customer, $password = null, $redirectUrl = '')
    {
        if ($password !== null) {
            $this->checkPasswordStrength($password);
            $customerEmail = $customer->getEmail();
            try {
                $this->credentialsValidator->checkPasswordDifferentFromEmail($customerEmail, $password);
            } catch (InputException $e) {
                throw new LocalizedException(
                    __("The password can't be the same as the email address. Create a new password and try again.")
                );
            }
            $hash = $this->createPasswordHash($password);
        } else {
            $hash = null;
        }
        return $this->createAccountWithPasswordHash($customer, $hash, $redirectUrl);
    }
    /**
     * Make sure that password complies with minimum security requirements.
     *
     * @param string $password
     * @return void
     * @throws InputException
     */
    protected function checkPasswordStrength($password)
    {
        $length = $this->stringHelper->strlen($password);
        if ($length > self::MAX_PASSWORD_LENGTH) {
            throw new InputException(
                __(
                    'Please enter a password with at most %1 characters.',
                    self::MAX_PASSWORD_LENGTH
                )
            );
        }
        $configMinPasswordLength = $this->getMinPasswordLength();
        if ($length < $configMinPasswordLength) {
            throw new InputException(
                __(
                    'The password needs at least %1 characters. Create a new password and try again.',
                    $configMinPasswordLength
                )
            );
        }
        if ($this->stringHelper->strlen(trim($password)) != $length) {
            throw new InputException(
                __("The password can't begin or end with a space. Verify the password and try again.")
            );
        }

        $requiredCharactersCheck = $this->makeRequiredCharactersCheck($password);
        if ($requiredCharactersCheck !== 0) {
            throw new InputException(
                __(
                    'Minimum of different classes of characters in password is %1.' .
                    ' Classes of characters: Lower Case, Upper Case, Digits, Special Characters.',
                    $requiredCharactersCheck
                )
            );
        }
    }
    /**
     * Retrieve minimum password length
     *
     * @return int
     */
    protected function getMinPasswordLength()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MINIMUM_PASSWORD_LENGTH);
    }
    /**
     * Check password for presence of required character sets
     *
     * @param string $password
     * @return int
     */
    protected function makeRequiredCharactersCheck($password)
    {
        $counter = 0;
        $requiredNumber = $this->scopeConfig->getValue(self::XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER);
        $return = 0;

        if (preg_match('/[0-9]+/', $password)) {
            $counter++;
        }
        if (preg_match('/[A-Z]+/', $password)) {
            $counter++;
        }
        if (preg_match('/[a-z]+/', $password)) {
            $counter++;
        }
        if (preg_match('/[^a-zA-Z0-9]+/', $password)) {
            $counter++;
        }

        if ($counter < $requiredNumber) {
            $return = $requiredNumber;
        }

        return $return;
    }
    /**
     * Create a hash for the given password
     *
     * @param string $password
     * @return string
     */
    protected function createPasswordHash($password)
    {
        return $this->encryptor->getHash($password, true);
    }

    /**
     * @inheritdoc
     *
     * @throws InputMismatchException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function createAccountWithPasswordHash(\Magento\Customer\Api\Data\CustomerInterface $customer, $hash, $redirectUrl = '')
    {
        // This logic allows an existing customer to be added to a different store.  No new account is created.
        // The plan is to move this logic into a new method called something like 'registerAccountWithStore'
        if ($customer->getId()) {
            $customer = $this->customerRepository->get($customer->getEmail());
            $websiteId = $customer->getWebsiteId();

            if ($this->isCustomerInStore($websiteId, $customer->getStoreId())) {
                throw new InputException(__('This customer already exists in this store.'));
            }
            // Existing password hash will be used from secured customer data registry when saving customer
        }

        // Make sure we have a storeId to associate this customer with.
        if (!$customer->getStoreId()) {//如果没有传店铺id
            if ($customer->getWebsiteId()) {//如果有站点，获取站点默认店铺
                $storeId = $this->storeManager->getWebsite($customer->getWebsiteId())->getDefaultStore()->getId();
            } else {
                $this->storeManager->setCurrentStore(null);
                $storeId = $this->storeManager->getStore()->getId();
            }
            $customer->setStoreId($storeId);
        }

        // Associate website_id with customer
        if (!$customer->getWebsiteId()) {
            $websiteId = $this->storeManager->getStore($customer->getStoreId())->getWebsiteId();
            $customer->setWebsiteId($websiteId);
        }

        $this->validateCustomerStoreIdByWebsiteId($customer);

        // Update 'created_in' value with actual store name
        if ($customer->getId() === null) {
            $storeName = $this->storeManager->getStore($customer->getStoreId())->getName();
            $customer->setCreatedIn($storeName);
        }

        $customerAddresses = $customer->getAddresses() ?: [];
        $customer->setAddresses(null);
        try {
            // If customer exists existing hash will be used by Repository
            $customer = $this->customerRepository->save($customer, $hash);
        } catch (AlreadyExistsException $e) {
            throw new InputMismatchException(
                __('A customer with the same email address already exists in an associated website.')
            );
        } catch (LocalizedException $e) {
            throw $e;
        }
        try {
            foreach ($customerAddresses as $address) {
                if (!$this->isAddressAllowedForWebsite($address, $customer->getStoreId())) {
                    continue;
                }
                if ($address->getId()) {
                    $newAddress = clone $address;
                    $newAddress->setId(null);
                    $newAddress->setCustomerId($customer->getId());
                    $this->addressRepository->save($newAddress);
                } else {
                    $address->setCustomerId($customer->getId());
                    $this->addressRepository->save($address);
                }
            }
            $this->customerRegistry->remove($customer->getId());
        } catch (InputException $e) {
            $this->customerRepository->delete($customer);
            throw $e;
        }
        $customer = $this->customerRepository->getById($customer->getId());
        $newLinkToken = $this->mathRandom->getUniqueHash();
        $this->changeResetPasswordLinkToken($customer, $newLinkToken);
        $this->sendEmailConfirmation($customer, $redirectUrl);

        return $customer;
    }

    /**
     * @inheritDoc
     */
    public function isCustomerInStore($customerWebsiteId, $storeId)
    {
        $ids = [];
        if ((bool)$this->configShare->isWebsiteScope()) {
            $ids = $this->storeManager->getWebsite($customerWebsiteId)->getStoreIds();
        } else {
            foreach ($this->storeManager->getStores() as $store) {
                $ids[] = $store->getId();
            }
        }

        return in_array($storeId, $ids);
    }

    /**
     * Validate customer store id by customer website id.
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return bool
     * @throws LocalizedException
     */
    public function validateCustomerStoreIdByWebsiteId(CustomerInterface $customer)
    {
        if (!$this->isCustomerInStore($customer->getWebsiteId(), $customer->getStoreId())) {
            throw new LocalizedException(__('The store view is not in the associated website.'));
        }

        return true;
    }
    /**
     * Check is address allowed for store
     *
     * @param AddressInterface $address
     * @param int|null $storeId
     * @return bool
     */
    private function isAddressAllowedForWebsite(AddressInterface $address, $storeId): bool
    {
        $allowedCountries = $this->allowedCountriesReader->getAllowedCountries(ScopeInterface::SCOPE_STORE, $storeId);

        return in_array($address->getCountryId(), $allowedCountries);
    }

    /**
     * Change reset password link token
     *
     * Stores new reset password link token
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param string $passwordLinkToken
     * @return bool
     * @throws InputException
     * @throws InputMismatchException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function changeResetPasswordLinkToken($customer, $passwordLinkToken)
    {
        if (!is_string($passwordLinkToken) || empty($passwordLinkToken)) {
            throw new InputException(
                __(
                    'Invalid value of "%value" provided for the %fieldName field.',
                    ['value' => $passwordLinkToken, 'fieldName' => 'password reset token']
                )
            );
        }
        if (is_string($passwordLinkToken) && !empty($passwordLinkToken)) {
            $customerSecure = $this->customerRegistry->retrieveSecureData($customer->getId());
            $customerSecure->setRpToken($passwordLinkToken);
            $customerSecure->setRpTokenCreatedAt(
                $this->dateTimeFactory->create()->format(DateTime::DATETIME_PHP_FORMAT)
            );
            $this->setIgnoreValidationFlag($customer);
            $this->customerRepository->save($customer);
        }
        return true;
    }
    /**
     * Send either confirmation or welcome email after an account creation
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param string $redirectUrl
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function sendEmailConfirmation(\Magento\Customer\Api\Data\CustomerInterface $customer, $redirectUrl)
    {
        try {
            $hash = $this->customerRegistry->retrieveSecureData($customer->getId())->getPasswordHash();
            $templateType = self::NEW_ACCOUNT_EMAIL_REGISTERED;
            if ($this->isConfirmationRequired($customer) && $hash != '') {
                $templateType = self::NEW_ACCOUNT_EMAIL_CONFIRMATION;
            } elseif ($hash == '') {
                $templateType = self::NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD;
            }
            $this->getEmailNotification()->newAccount($customer, $templateType, $redirectUrl, $customer->getStoreId());
            $customer->setConfirmation(null);
        } catch (MailException $e) {
            // If we are not able to send a new account email, this should be ignored
            $this->logger->critical($e);
        } catch (\UnexpectedValueException $e) {
            $this->logger->error($e);
        }
    }
    /**
     * Check if accounts confirmation is required in config
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return bool
     * @deprecated 101.0.4
     * @see AccountConfirmation::isConfirmationRequired
     */
    protected function isConfirmationRequired($customer)
    {
        return $this->accountConfirmation->isConfirmationRequired(
            $customer->getWebsiteId(),
            $customer->getId(),
            $customer->getEmail()
        );
    }

    /**
     * Set ignore_validation_flag for reset password flow to skip unnecessary address and customer validation
     *
     * @param \Magento\Customer\Model\Customer $customer
     * @return void
     */
    private function setIgnoreValidationFlag($customer)
    {
        $customer->setData('ignore_validation_flag', true);
    }

    /**
     * Get email notification
     *
     * @return EmailNotificationInterface
     * @deprecated 100.1.0
     */
    private function getEmailNotification()
    {
        if (!($this->emailNotification instanceof EmailNotificationInterface)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(
                EmailNotificationInterface::class
            );
        } else {
            return $this->emailNotification;
        }
    }

}
