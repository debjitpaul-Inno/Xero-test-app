<?php

namespace App\Action;

use DateTime;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use XeroAPI\XeroPHP\AccountingObjectSerializer;
use XeroAPI\XeroPHP\Api\AccountingApi;
use XeroAPI\XeroPHP\Api\AssetApi;
use XeroAPI\XeroPHP\Api\IdentityApi;
use XeroAPI\XeroPHP\Api\ProjectApi;
use XeroAPI\XeroPHP\Configuration;
use XeroAPI\XeroPHP\Models\Accounting\Contact;
use XeroAPI\XeroPHP\Models\Accounting\ContactPerson;
use XeroAPI\XeroPHP\Models\Accounting\Contacts;
use XeroAPI\XeroPHP\Models\Accounting\Invoice;
use XeroAPI\XeroPHP\Models\Accounting\Invoices;
use XeroAPI\XeroPHP\Models\Accounting\LineItem;

class AuthorizationResource
{
    public function execute()
    {
        ini_set('display_errors', 'On');
//        require __DIR__ . '/vendor/autoload.php';
        require_once('storage.php');

        // Use this class to deserialize error caught


        // Storage Classe uses sessions for storing token > extend to your DB of choice
//        $storage = new StorageClass();
        $storage = (new Storage());
        $xeroTenantId = (string)$storage->getSession()['tenant_id'];
//        dd($xeroTenantId);

        if ($storage->getHasExpired()) {
            $provider = new \League\OAuth2\Client\Provider\GenericProvider([
                'clientId'                => '6B907BAABFF347299F00D976D2D35EA5',
                'clientSecret'            => 'VGkl5Pa1d2_LuQYowpNM0cjjVDdf_q02FJphAFwS4OHuRtjH',
                'redirectUri'             => route('callback'),
                'urlAuthorize'            => 'https://login.xero.com/identity/connect/authorize',
                'urlAccessToken'          => 'https://identity.xero.com/connect/token',
                'urlResourceOwnerDetails' => 'https://identity.xero.com/resources'
            ]);

            $newAccessToken = $provider->getAccessToken('refresh_token', [
                'refresh_token' => $storage->getRefreshToken()
            ]);

            // Save my token, expiration and refresh token
            $storage->setToken(
                $newAccessToken->getToken(),
                $newAccessToken->getExpires(),
                $xeroTenantId,
                $newAccessToken->getRefreshToken(),
                $newAccessToken->getValues()["id_token"] );
        }

        $config = Configuration::getDefaultConfiguration()->setAccessToken( (string)$storage->getSession()['token'] );


        $accountingApi = new AccountingApi(
//            new GuzzleHttp\Client(),
            new Client(),
            $config
        );

        $assetApi = new AssetApi(
//            new GuzzleHttp\Client(),
            new Client(),
            $config
        );

//        $identityApi = new XeroAPI\XeroPHP\Api\IdentityApi(
        $identityApi = new IdentityApi(
//            new GuzzleHttp\Client(),
            new Client(),
            $config
        );

//        $projectApi = new XeroAPI\XeroPHP\Api\ProjectApi(
        $projectApi = new ProjectApi(
//            new GuzzleHttp\Client(),
            new Client(),
            $config
        );

        $message = "no API calls";
//        if (isset($_GET['action'])) {
//            if ($_GET["action"] == 1) {
//                // Get Organisation details
//                $apiResponse = $accountingApi->getOrganisations($xeroTenantId);
//                $message = 'Organisation Name: ' . $apiResponse->getOrganisations()[0]->getName();
//            } else if ($_GET["action"] == 2) {
//                // Create Contact
//                try {
////                    $person = new XeroAPI\XeroPHP\Models\Accounting\ContactPerson;
//                    $person = new ContactPerson();
//                    $person->setFirstName("John")
//                        ->setLastName("Smith")
//                        ->setEmailAddress("john.smith@24locks.com")
//                        ->setIncludeInEmails(true);
//
//                    $arr_persons = [];
//                    array_push($arr_persons, $person);
//
////                    $contact = new XeroAPI\XeroPHP\Models\Accounting\Contact;
//                    $contact = new Contact();
//                    $contact->setName('FooBar')
//                        ->setFirstName("Foo")
//                        ->setLastName("Bar")
//                        ->setEmailAddress("ben.bowden@24locks.com")
//                        ->setContactPersons($arr_persons);
//
//                    $arr_contacts = [];
//                    array_push($arr_contacts, $contact);
////                    $contacts = new XeroAPI\XeroPHP\Models\Accounting\Contacts;
//                    $contacts = new Contacts();
//                    $contacts->setContacts($arr_contacts);
//
//                    $apiResponse = $accountingApi->createContacts($xeroTenantId,$contacts);
//                    $message = 'New Contact Name: ' . $apiResponse->getContacts()[0]->getName();
//                } catch (\XeroAPI\XeroPHP\ApiException $e) {
//                    $error = AccountingObjectSerializer::deserialize(
//                        $e->getResponseBody(),
//                        '\XeroAPI\XeroPHP\Models\Accounting\Error',
//                        []
//                    );
//                    $message = "ApiException - " . $error->getElements()[0]["validation_errors"][0]["message"];
//                }
//            } else if ($_GET["action"] == 3) {
//                $if_modified_since = new \DateTime("2019-01-02T19:20:30+01:00"); // \DateTime | Only records created or modified since this timestamp will be returned
//                $if_modified_since = null;
//                $where = 'Type=="ACCREC"'; // string
//                $where = null;
//                $order = null; // string
//                $ids = null; // string[] | Filter by a comma-separated list of Invoice Ids.
//                $invoice_numbers = null; // string[] |  Filter by a comma-separated list of Invoice Numbers.
//                $contact_ids = null; // string[] | Filter by a comma-separated list of ContactIDs.
//                $statuses = array("DRAFT", "SUBMITTED");;
//                $page = 1; // int | e.g. page=1 – Up to 100 invoices will be returned in a single API call with line items
//                $include_archived = null; // bool | e.g. includeArchived=true - Contacts with a status of ARCHIVED will be included
//                $created_by_my_app = null; // bool | When set to true you'll only retrieve Invoices created by your app
//                $unitdp = null; // int | e.g. unitdp=4 – You can opt in to use four decimal places for unit amounts
//
//                try {
//                    $apiResponse = $accountingApi->getInvoices($xeroTenantId, $if_modified_since, $where, $order, $ids, $invoice_numbers, $contact_ids, $statuses, $page, $include_archived, $created_by_my_app, $unitdp);
//                    if ( count($apiResponse->getInvoices()) > 0 ) {
//                        $message = 'Total invoices found: ' . count($apiResponse->getInvoices());
//                    } else {
//                        $message = "No invoices found matching filter criteria";
//                    }
//                } catch (Exception $e) {
//                    echo 'Exception when calling AccountingApi->getInvoices: ', $e->getMessage(), PHP_EOL;
//                }
//            } else if ($_GET["action"] == 4) {
//                // Create Multiple Contacts
//                try {
////                    $contact = new XeroAPI\XeroPHP\Models\Accounting\Contact;
//                    $contact = new Contact();
//                    $contact->setName('George Jetson')
//                        ->setFirstName("George")
//                        ->setLastName("Jetson")
//                        ->setEmailAddress("george.jetson@aol.com");
//
//                    // Add the same contact twice - the first one will succeed, but the
//                    // second contact will throw a validation error which we'll catch.
//                    $arr_contacts = [];
//                    array_push($arr_contacts, $contact);
//                    array_push($arr_contacts, $contact);
////                    $contacts = new XeroAPI\XeroPHP\Models\Accounting\Contacts;
//                    $contacts = new Contacts();
//                    $contacts->setContacts($arr_contacts);
//
//                    $apiResponse = $accountingApi->createContacts($xeroTenantId,$contacts,false);
//                    $message = 'First contacts created: ' . $apiResponse->getContacts()[0]->getName();
//
//                    if ($apiResponse->getContacts()[1]->getHasValidationErrors()) {
//                        $message = $message . '<br> Second contact validation error : ' . $apiResponse->getContacts()[1]->getValidationErrors()[0]["message"];
//                    }
//                } catch (\XeroAPI\XeroPHP\ApiException $e) {
//                    $error = AccountingObjectSerializer::deserialize(
//                        $e->getResponseBody(),
//                        '\XeroAPI\XeroPHP\Models\Accounting\Error',
//                        []
//                    );
//                    $message = "ApiException - " . $error->getElements()[0]["validation_errors"][0]["message"];
//                }
//            } else if ($_GET["action"] == 5) {
//                // DELETE the org FIRST Connection returned
//                $connections = $identityApi->getConnections();
//                $id = $connections[0]->getId();
//                $result = $identityApi->deleteConnection($id);
//            }
//        }

        $summarizeErrors = true;
        $unitdp = 2;
        $contact_id = '';
        $dateValue = new DateTime('2020-10-10');
        $dueDateValue = new DateTime('2020-10-28');

        $contact = new Contact();
        $contact->setName('Sharon paul');
        $contact->setFirstName('Sharon');
        $contact->setLastName('paul');
        $contact->setEmailAddress('hulk1@avengers.com');

        $contacts = new Contacts();
        $arr_contacts = [];
        array_push($arr_contacts, $contact);
        $contacts->setContacts($arr_contacts);
        try {
            $result = $accountingApi->createContacts($xeroTenantId, $contacts, $summarizeErrors);
            $contact_id = $result->getContacts()[0]->getContactId();
//            $get_contact = $accountingApi->getContact($xeroTenantId,$contact->contact_id);

        } catch (Exception $e) {
            echo 'Exception when calling AccountingApi->createContacts: ', $e->getMessage(), PHP_EOL;
        }

        $contact = new Contact();
        $contact->setContactID($contact_id);
//
//        $lineItemTracking = new XeroAPI\XeroPHP\Models\Accounting\LineItemTracking;
//        $lineItemTracking->setTrackingCategoryID('00000000-0000-0000-0000-000000000000');
//        $lineItemTracking->setTrackingOptionID('00000000-0000-0000-0000-000000000000');
//        $lineItemTrackings = [];
//        array_push($lineItemTrackings, $lineItemTracking);
//
        $lineItem = new LineItem();
        $lineItem->setDescription('Foobar  2');
        $lineItem->setQuantity(2.0);
        $lineItem->setUnitAmount(20.0);
        $lineItem->setAccountCode('200');

//        $lineItem->setTracking(lineItemTrackings);
        $lineItem->setTaxAmount(12);
        $lineItems = [];
        array_push($lineItems, $lineItem);

        $invoice = new Invoice();
        $invoice->setCurrencyCode('SGD');
        $invoice->setType(Invoice::TYPE_ACCREC);
        $invoice->setContact($contact);
        $invoice->setDate($dateValue);
        $invoice->setDueDate($dueDateValue);
        $invoice->setLineItems($lineItems);
        $invoice->setReference('Subscription Charge');
        $invoice->setStatus(Invoice::STATUS_DRAFT);
//
        $invoices = new Invoices();
        $arr_invoices = [];
        array_push($arr_invoices, $invoice);
        $invoices->setInvoices($arr_invoices);

        try {
            $result = $accountingApi->createInvoices($xeroTenantId, $invoices, $summarizeErrors, $unitdp);
            $invoice = $result->getInvoices()[0]->getLineItems()[0];
            dd($invoice);
        } catch (Exception $e) {
            echo 'Exception when calling AccountingApi->createInvoices: ', $e->getMessage(), PHP_EOL;
        }

    }

}
