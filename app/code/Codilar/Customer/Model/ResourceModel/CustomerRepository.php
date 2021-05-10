<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codilar\Customer\Model\ResourceModel;


use Codilar\Customer\Api\CustomerRepositoryInterface;
use Codilar\Customer\Api\Data\CustomerInterface;
use Codilar\Customer\Model\CustomerRegistry;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\Customer\NotificationStorage;
use Codilar\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Delegation\Data\NewOperation;
use Magento\Customer\Model\Delegation\Storage as DelegatedStorage;
use Magento\Customer\Model\ResourceModel\AddressRepository;
use Magento\Framework\Api\ImageProcessorInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Codilar\Customer\Model\Customer as CustomerModel;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Codilar\Customer\Model\ResourceModel\Customer as CustomerResource;


/**
 * Customer repository.
 *
 * CRUD operations for customer entity
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class CustomerRepository implements CustomerRepositoryInterface
{

    /**
     * @var DelegatedStorage
     */
    private  $delegatedStorage;
    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;
    /**
     * @var NotificationStorage
     */
    private $notificationStorage;
    /**
     * @var ImageProcessorInterface
     */
    private $imageProcessor;

    /**
     * @var ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @var AddressRepository
     */
    protected $addressRepository;

    /**
     * @var ManagerInterface
     */
    protected $eventManager;
    private \Codilar\Customer\Model\ResourceModel\Customer $customerResource;

    public function __construct(
        DelegatedStorage $delegatedStorage,
        CustomerRegistry $customerRegistry,
        NotificationStorage $notificationStorage,
        ImageProcessorInterface $imageProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        StoreManagerInterface $storeManager,
        CustomerFactory $customerFactory,
        AddressRepository $addressRepository,
        ManagerInterface $eventManager,
        CustomerResource $customerResource,
        ?GroupRepositoryInterface $groupRepository = null
    ) {

        $this->delegatedStorage = $delegatedStorage;
        $this->customerRegistry = $customerRegistry;
        $this->notificationStorage = $notificationStorage;
        $this->imageProcessor = $imageProcessor;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $this->customerFactory = $customerFactory;
        $this->groupRepository = $groupRepository ?: ObjectManager::getInstance()->get(GroupRepositoryInterface::class);
        $this->addressRepository = $addressRepository;
        $this->customerResource = $customerResource;
    }

    /**
     * Create or update a customer.
     *
     * @param \Codilar\Customer\Api\Data\CustomerInterface $customer
     * @param string $passwordHash
     * @return \Codilar\Customer\Api\Data\CustomerInterface
     * @throws \Magento\Framework\Exception\InputException If bad input is provided
     * @throws \Magento\Framework\Exception\State\InputMismatchException If the provided email is already used
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function save(CustomerInterface $customer, $passwordHash = null)
    {
        /** @var NewOperation|null $delegatedNewOperation */
        $delegatedNewOperation = !$customer->getId() ? $this->delegatedStorage->consumeNewOperation() : null;
        $prevCustomerData = $prevCustomerDataArr = null;
        if ($customer->getId()) {
            $prevCustomerData = $this->getById($customer->getId());
            $prevCustomerDataArr = $prevCustomerData->__toArray();
        }
        /** @var $customer \Codilar\Customer\Model\Data\Customer */
        $customerArr = $customer->__toArray();
        $customer = $this->imageProcessor->save(
            $customer,
            CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            $prevCustomerData
        );
        $origAddresses = $customer->getAddresses();
        $customer->setAddresses([]);
        $customerData = $this->extensibleDataObjectConverter->toNestedArray($customer, [], CustomerInterface::class);
        $customer->setAddresses($origAddresses);
        /** @var CustomerModel $customerModel */

        $customerModel = $this->customerFactory->create(['data' => $customerData]);
        //Model's actual ID field maybe different than "id" so "id" field from $customerData may be ignored.
        $customerModel->setId($customer->getId());
        $storeId = $customerModel->getStoreId();
        if ($storeId === null) {
            $customerModel->setStoreId(
                $prevCustomerData ? $prevCustomerData->getStoreId() : $this->storeManager->getStore()->getId()
            );
        }
        $this->validateGroupId($customer->getGroupId());
        $this->setCustomerGroupId($customerModel, $customerArr, $prevCustomerDataArr);
        // Need to use attribute set or future updates can cause data loss
        if (!$customerModel->getAttributeSetId()) {
            $customerModel->setAttributeSetId(CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER);
        }
        $this->populateCustomerWithSecureData($customerModel, $passwordHash);
        // If customer email was changed, reset RpToken info
        if ($prevCustomerData && $prevCustomerData->getEmail() !== $customerModel->getEmail()) {
            $customerModel->setRpToken(null);
            $customerModel->setRpTokenCreatedAt(null);
        }
        if (!array_key_exists('addresses', $customerArr)
            && null !== $prevCustomerDataArr
            && array_key_exists('default_billing', $prevCustomerDataArr)
        ) {
            $customerModel->setDefaultBilling($prevCustomerDataArr['default_billing']);
        }
        if (!array_key_exists('addresses', $customerArr)
            && null !== $prevCustomerDataArr
            && array_key_exists('default_shipping', $prevCustomerDataArr)
        ) {
            $customerModel->setDefaultShipping($prevCustomerDataArr['default_shipping']);
        }
        $this->setValidationFlag($customerArr, $customerModel);

        $customerModel->save();

        $this->customerRegistry->push($customerModel);
        $customerId = $customerModel->getId();
        if (!$customer->getAddresses()
            && $delegatedNewOperation
            && $delegatedNewOperation->getCustomer()->getAddresses()
        ) {
            $customer->setAddresses($delegatedNewOperation->getCustomer()->getAddresses());
        }
        if ($customer->getAddresses() !== null && !$customerModel->getData('ignore_validation_flag')) {
            if ($customer->getId()) {
                $existingAddresses = $this->getById($customer->getId())->getAddresses();
                $getIdFunc = function ($address) {
                    return $address->getId();
                };
                $existingAddressIds = array_map($getIdFunc, $existingAddresses);
            } else {
                $existingAddressIds = [];
            }
            $savedAddressIds = [];
            foreach ($customer->getAddresses() as $address) {
                $address->setCustomerId($customerId)
                    ->setRegion($address->getRegion());
                $this->addressRepository->save($address);
                if ($address->getId()) {
                    $savedAddressIds[] = $address->getId();
                }
            }
            $this->deleteAddressesByIds(array_diff($existingAddressIds, $savedAddressIds));
        }
        $this->customerRegistry->remove($customerId);
        $savedCustomer = $this->get($customer->getEmail(), $customer->getWebsiteId());
        $this->eventManager->dispatch(
            'customer_save_after_data_object',
            [
                'customer_data_object' => $savedCustomer,
                'orig_customer_data_object' => $prevCustomerData,
                'delegate_data' => $delegatedNewOperation ? $delegatedNewOperation->getAdditionalData() : [],
            ]
        );
        return $savedCustomer;
    }

    /**
     * Get customer by Customer ID.
     *
     * @param int $customerId
     * @return \Codilar\Customer\Api\Data\CustomerInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If customer with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($customerId)
    {
        $customerModel = $this->customerRegistry->retrieve($customerId);
        return $customerModel->getDataModel();
    }

    /**
     * Retrieve customer.
     *
     * @param string $email
     * @param int|null $websiteId
     * @return \Codilar\Customer\Api\Data\CustomerInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If customer with the specified email does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($email, $websiteId = null)
    {
        $customerModel = $this->customerRegistry->retrieveByEmail($email, $websiteId);
        return $customerModel->getDataModel();
    }

    /**
     * Delete customer.
     *
     * @param \Codilar\Customer\Api\Data\CustomerInterface $customer
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Codilar\Customer\Api\Data\CustomerInterface $customer)
    {
        return $this->deleteById($customer->getId());
    }

    /**
     * Delete customer by Customer ID.
     *
     * @param int $customerId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($customerId)
    {
        $customerModel = $this->customerRegistry->retrieve($customerId);
        $customerModel->delete();
        $this->customerRegistry->remove($customerId);
        $this->notificationStorage->remove(NotificationStorage::UPDATE_CUSTOMER_SESSION, $customerId);

        return true;
    }
    /**
     * Validate customer group id if exist
     *
     * @param int|null $groupId
     * @return bool
     * @throws LocalizedException
     */
    private function validateGroupId(?int $groupId): bool
    {
        if ($groupId) {
            try {
                $this->groupRepository->getById($groupId);
            } catch (NoSuchEntityException $e) {
                throw new LocalizedException(__('The specified customer group id does not exist.'));
            }
        }

        return true;
    }

    /**
     * Set customer group id
     *
     * @param Customer $customerModel
     * @param array $customerArr
     * @param array $prevCustomerDataArr
     */
    private function setCustomerGroupId($customerModel, $customerArr, $prevCustomerDataArr)
    {
        if (!isset($customerArr['group_id']) && $prevCustomerDataArr && isset($prevCustomerDataArr['group_id'])) {
            $customerModel->setGroupId($prevCustomerDataArr['group_id']);
        }
    }

    /**
     * Set ignore_validation_flag to skip model validation
     *
     * @param array $customerArray
     * @param Customer $customerModel
     * @return void
     */
    private function setValidationFlag($customerArray, $customerModel)
    {
        if (isset($customerArray['ignore_validation_flag'])) {
            $customerModel->setData('ignore_validation_flag', true);
        }
    }

    /**
     * Delete addresses by ids
     *
     * @param array $addressIds
     * @return void
     */
    private function deleteAddressesByIds(array $addressIds): void
    {
        foreach ($addressIds as $id) {
            $this->addressRepository->deleteById($id);
        }
    }

    /**
     * Set secure data to customer model
     *
     * @param \Codilar\Customer\Model\Customer $customerModel
     * @param string|null $passwordHash
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @return void
     */
    private function populateCustomerWithSecureData($customerModel, $passwordHash = null)
    {
        if ($customerModel->getId()) {
            $customerSecure = $this->customerRegistry->retrieveSecureData($customerModel->getId());

            $customerModel->setRpToken($passwordHash ? null : $customerSecure->getRpToken());
            $customerModel->setRpTokenCreatedAt($passwordHash ? null : $customerSecure->getRpTokenCreatedAt());
            $customerModel->setPasswordHash($passwordHash ?: $customerSecure->getPasswordHash());

            $customerModel->setFailuresNum($customerSecure->getFailuresNum());
            $customerModel->setFirstFailure($customerSecure->getFirstFailure());
            $customerModel->setLockExpires($customerSecure->getLockExpires());
        } elseif ($passwordHash) {
            $customerModel->setPasswordHash($passwordHash);
        }

        if ($passwordHash && $customerModel->getId()) {
            $this->customerRegistry->remove($customerModel->getId());
        }
    }
}
