<?php namespace QR;
/**
 * Author: lori@flashbay.com
 *
 **/
use QR\Cards\OrderCard;
/**
 *
 * @WARNING: NEVER change below codes unless you're clear what you are doing
 *
 **/
class VariableManager
{
    static private $_varEntityMulti = ['accessory', 'service',];
    static private $_varSet = [
        'card' => [
            'name'    => 'card',
            'text'    => 'Craft Card Of Current Item(If Already Exists)',
            'attrSet' => [
                'id'         => ['name' => 'id', 'text' => 'Craft Card ID',],
                'type'       => ['name' => 'type', 'text' => 'Card Type',],
                'comment'    => ['name' => 'comment', 'text' => 'Craft Card\'s Comments',],
                'order_id'   => ['name' => 'order_id', 'text' => 'Craft Card\'s Sales Order\'s Internal ID',],
                'salesorder' => ['name' => 'salesorder', 'text' => 'Old Style Craft Card ID(For backward compatibility)',],
                'item_id'    => ['name' => 'item_id', 'text' => 'Craft Card\'s Item\'s ID',],
                'proof_id'   => ['name' => 'proof_id', 'text' => 'Craft Card\'s Item\'s Virtual Proof ID',],
                'status'     => ['name' => 'status', 'text' => 'Craft Card\'s Status(0: created, 1: viewed, 2: printed, 3: done)',],
                //'memory'     => ['name' => 'memory', 'text' => 'Craft Card\'s Item\'s Memory/Construction Chosen',],
                'qty'        => ['name' => 'qty', 'text' => 'Craft Card\'s Item\'s Quantity',],
                'parent'     => ['name' => 'parent', 'text' => 'Craft Card\'s Parent Card ID',],
                'created_at' => ['name' => 'created_at', 'text' => 'Craft Card\'s Creating Timestamp',],
                'updated_at' => ['name' => 'updated_at', 'text' => 'Craft Card\'s Latest Updating Timestamp',],
                //Extended attribute
                'item_num'   => ['name' => 'item_num', 'text' => 'Craft Card\'s Items Number In Group',],
            ],
        ],
        'card_type' => [
            'name' => 'card_type',
            'text' => 'Craft Card Type Info of Current Item(If Already Exists)',
            'attrSet' => [
                'id'      => ['name' => 'id', 'text' => 'Card Type ID',],
                'name'    => ['name' => 'name', 'text' => 'Card Type name',],
                'type'    => ['name' => 'type', 'text' => 'Card Type Category(0: Order Control, 1: Stock Control, 2: Stock Custom)',],
                'status'  => ['name' => 'status', 'text' => 'Card Type Status',],
            ],
        ],
        'order' => [
            'name'    => 'order',
            'text'    => 'Sales Order Of Current Item',
            'attrSet' => [
                //Native Attributes
                'internalID'         => ['name' => 'internalID', 'text' => 'Order ID',],
                'salesOrderNumber'   => ['name' => 'salesOrderNumber', 'text' => 'Order Number(e.g. S1234)',],
                'currency'           => ['name' => 'currency', 'text' => 'Order Currency',],
                'customerID'         => ['name' => 'customerID', 'text' => 'Customer ID',],
                'orderDate'          => ['name' => 'orderDate', 'text' => 'Order Date',],
                'deadline'           => ['name' => 'deadline', 'text' => 'Order Dead Line',],
                'subTotal'           => ['name' => 'subTotal', 'text' => 'Order\'s Subtotal',],
                'printDeliveryNotes' => ['name' => 'printDeliveryNotes', 'text' => 'Order Print Delivery Notes?',],
                'status'             => ['name' => 'status', 'text' => 'Order Status',],
                'processingStatus'   => ['name' => 'processingStatus', 'text' => 'Order Processing Status',],
                'shippingDate'       => ['name' => 'shippingDate', 'text' => 'Order Shipping Date',],
                'shipped'            => ['name' => 'shipped', 'text' => 'Order Shipped Date',],
                'lastModifiedDate'   => ['name' => 'lastModifiedDate', 'text' => 'Order Last Modified Date',],
                'shippingCountry'    => ['name' => 'shippingCountry', 'text' => 'Order Shipping Country',],
                'billingCountry'     => ['name' => 'billingCountry', 'text' => 'Order Billing Country',],
                'billingAddress'     => ['name' => 'billingAddress', 'text' => 'Order Billing Address',],
                'shippingAddress'    => ['name' => 'shippingAddress', 'text' => 'Order Shipping Address',],
                'hasDataPreload'     => ['name' => 'hasDataPreload', 'text' => 'Order Has Data Preload?',],
                'location'           => ['name' => 'location', 'text' => 'Order Location',],
                'specialFlash'       => ['name' => 'specialFlash', 'text' => 'Special Flash'],
                'usingPCBA'          => ['name' => 'usingPCBA', 'text' => 'Using PCBA?',],
                'factoryNotes'       => ['name' => 'factoryNotes', 'text' => 'Factory Notes',],
                'printDeliveryNotes' => ['name' => 'printDeliveryNotes', 'text' => 'Print Delivery Notes?',],
                'branding'           => ['name' => 'branding', 'text' => 'Possible Branding Methods'],
                'dpHold'             => ['name' => 'dpHold', 'text' => 'Is Hold On Due to Data Preload?',],
                'lastUpdated'        => ['name' => 'lastUpdated', 'text' => 'Latest Update Time',],
                'approved'           => ['name' => 'approved', 'text' => 'Approved Datetime Of Order',],
                'created'            => ['name' => 'created', 'text' => 'Created Datetime Of Order',],
                'shebo'              => ['name' => 'shebo', 'text' => 'ShellBody Order? 0:No, 1: mUDP, 2: UDP',],
                //Extended Attributes
            ],
        ],
        'customer' => [
            'name'    => 'customer',
            'text'    => 'Order\'s Customer',
            'attrSet' => [
                //Native Attributes
                'internalID'    => ['name' => 'internalID', 'text' => 'Customer ID',],
                'name'          => ['name' => 'name', 'text' => 'Customer Name',],
                'country'       => ['name' => 'country', 'text' => 'Customer\'s Country',],
                'countryCode'   => ['name' => 'countryCode', 'text' => 'Customer\'s Country Code',],
                'priceLevel'    => ['name' => 'priceLevel', 'text' => 'Customer\'s USB Drive Price Level',],
                'pbPriceLevel'  => ['name' => 'pbPriceLevel', 'text' => 'Customer\'s Powerbank Price Level',],
                'category'      => ['name' => 'category', 'text' => 'Customer\'s Category',],
                'email'         => ['name' => 'email', 'text' => 'Customer\'s Email Address',],
                'vatNumber'     => ['name' => 'vatNumber', 'text' => 'Customer\'s VAT Number',],
                'websiteAddress'=> ['name' => 'websiteAddress', 'text' => 'Customer\'s Web Site',],
                'refNum'        => ['name' => 'refNum', 'text' => 'Customer\'s RefName',],
                //Extended Attributes
                'hasPrintFailOrder' => ['name' => 'hasPrintFailOrder', 'text' => 'Customer Has Print Fail History Order?', 'isExt' => true,],
                'isNew'             => ['name' => 'isNew', 'text' => 'Customer Is New?', 'isExt' => true,],
            ],
        ],
        'item' => [
            'name'    => 'item',
            'text'    => 'Current Item',
            'attrSet' => [
                //Native Attributes
                'itemIndex'         => ['name' => 'itemIndex', 'text' => 'Item ID',],
                'internalID'        => ['name' => 'internalID', 'text' => 'Item Internal ID',],
                'line'              => ['name' => 'line', 'text' => 'Item\'s Line',],
                'name'              => ['name' => 'name', 'text' => 'Item\'s Full Name',],
                'quantity'          => ['name' => 'quantity', 'text' => 'Item\'s Qty',],
                'serializedOptions' => ['name' => 'serializedOptions', 'text' => 'Serialized Options',],
                'virtualProof'      => ['name' => 'virtualProof', 'text' => 'Item virtual Proof ID',],
                'category'          => ['name' => 'category', 'text' => 'Item\'s Category String(Setting In DB)',],
                'options'           => ['name' => 'options', 'text' => 'Item\'s Options',],
                'description'       => ['name' => 'description', 'text' => 'Item\'s Description',],
                'tags'              => ['name' => 'tags', 'text' => 'Item\'s Tags',],
                'difficulty'        => ['name' => 'difficulty', 'text' => 'Item\'s Difficulty',],
                //Extended Attributes
                'unserializedOptions'     => ['name' => 'unserializedOptions', 'text' => 'Unserialized Options', 'isExt' => true,],
                'code'         => ['name' => 'code', 'text' => 'Product Code', 'isExt' => true,],
                'code2'        => ['name' => 'code2', 'text' => 'Product Code2', 'isExt' => true,],
                'capacity'     => ['name' => 'capacity', 'text' => 'Product\'s Capacity', 'isExt' => true,],
                'color'        => ['name' => 'color', 'text' => 'Product Color', 'isExt' => true,],
                'interface'    => ['name' => 'interface', 'text' => 'Product Interface', 'isExt' => true,],
                'type'         => ['name' => 'type', 'text' => 'Product Type(USB/Accessory/Powerbank/Audio/Service)', 'isExt' => true,],//USB/Accessory/Powerbank/Audio etc.
                'machine'      => ['name' => 'machine', 'text' => 'Product Machine Chosen', 'isExt' => true,],
                'memory'       => ['name' => 'memory', 'text' => 'Product Construction Chosen(Alias of construction)', 'isExt' => true,],
                'construction' => ['name' => 'construction', 'text' => 'Product Construction Chosen', 'isExt' => true,],
                'mergeKey'     => ['name' => 'mergeKey', 'text' => 'Merged Group key', 'isExt' => true,],
                'mergeKeyHash' => ['name' => 'mergeKeyHash', 'text' => 'Hash(md5) of Merged Group key', 'isExt' => true,],
                'merged' => ['name' => 'merged', 'text' => 'Merged By Items...', 'isExt' => true,],
            ],
        ],
        'virtualProof' => [
            'name'    => 'virtualProof',
            'text'    => 'Virtual Proof Of Current Item',
            'attrSet' => [
                //Native Attributes
                'proofName'           => ['name' => 'proofName', 'text' => 'Proof Name',],
                'imageURL'            => ['name' => 'imageURL', 'text' => 'Image URL',],
                'brandingMethod'      => ['name' => 'brandingMethod', 'text' => 'Branding Method',],
                'pantones'            => ['name' => 'pantones', 'text' => 'Proof Pantone',],
                'productColour'       => ['name' => 'productColour', 'text' => 'Product Proof Colour',],
                'internalID'          => ['name' => 'internalID', 'text' => 'Proof ID',],
                'textInProof'         => ['name' => 'textInProof', 'text' => 'Text In Proof',],
                'identicalScreens'    => ['name' => 'identicalScreens', 'text' => 'Is Position Color Identical For Screen Printing?',],
                'isUVPrint'           => ['name' => 'isUVPrint', 'text' => 'Is UV Print',],
                'forceSpr'            => ['name' => 'forceSpr', 'text' => 'Force Creating Printing Card Instead of UVP',],
                //Extended Attributes
                'positionSetting'     => ['name' => 'positionSetting', 'text' => 'Pantone Setting Of Each Postion', 'isExt' => true,],
                'positionNum'         => ['name' => 'positionNum', 'text' => 'Number of Pantone Color Positions', 'isExt' => true,],
                'engravingNum'        => ['name' => 'engravingNum', 'text' => 'Number of Engraving', 'isExt' => true,],
                'positionEngraving'   => ['name' => 'positionEngraving', 'text' => 'Engraving or Not of Each Position', 'isExt' => true,],
                'noEngravingNum'      => ['name' => 'noEngravingNum', 'text' => 'Non-empty Position Num After Engraving Mark(EMB|ENL|END) Removed', 'isExt' => true,],
                'positionNoEngraving' => ['name' => 'positionNoEngraving', 'text' => 'Non-empty or empty of Each Position After Engraving Mark(EMB|ENL|END) Removed ?', 'isExt' => true,],
                'positionColorsNum'   => ['name' => 'positionColorsNum', 'text' => 'Color Number of Each Pantone Position', 'isExt' => true,],
                'positionColors'      => ['name' => 'positionColors', 'text' => 'Colors of Each Pantone Position', 'isExt' => true,],
                'colorsNum'           => ['name' => 'colorsNum', 'text' => 'Number of Pantone Colors', 'isExt' => true,],
                'colorsNumX'          => ['name' => 'colorsNumX', 'text' => 'Number of Distinct Pantone Colors', 'isExt' => true,],
                'isUVPantoneColor'    => ['name' => 'isUVPantoneColor', 'text' => 'Pantone Color Enough For UV?', 'isExt' => true,],
                'brandingMethodTxt'   => ['name' => 'brandingMethodTxt', 'text' => 'Branding Method Text', 'isExt' => true,],
                'brandingMethodTxtArray' => ['name' => 'brandingMethodTxtArray', 'text' => 'Branding Method Text Array', 'isExt' => true,],
                'isUsedInPreviousOrder'  => ['name' => 'isUsedInPreviousOrder', 'text' => 'Already Used In Historical Orders?', 'isExt' => true,],
                'hasHistoryOrder' => ['name' => 'hasHistoryOrder', 'text' => 'Has History Order With This Virtual Proof?', 'isExt' => true,],
            ],
        ],
        'model' => [
            'name'    => 'model',
            'text'    => 'Model Of Current Item',
            'attrSet' => [
                //Native Attributes
                'itemIndex'         => ['name' => 'itemIndex', 'text' => 'Item ID',],
                'line'              => ['name' => 'line', 'text' => 'Item\'s Line',],
                'name'              => ['name' => 'name', 'text' => 'Item\'s Full Name',],
                'quantity'          => ['name' => 'quantity', 'text' => 'Item\'s Qty',],
                'serializedOptions' => ['name' => 'serializedOptions', 'text' => 'Serialized Options',],
                'category'          => ['name' => 'category', 'text' => 'Item\'s Category String(Setting In DB)',],
                'options'           => ['name' => 'options', 'text' => 'Item\'s Options',],
                'description'       => ['name' => 'description', 'text' => 'Item\'s Description',],
                'tags'              => ['name' => 'tags', 'text' => 'Item\'s Tags',],
                'difficulty'        => ['name' => 'difficulty', 'text' => 'Item\'s Difficulty',],
                //Extended Attributes
                'unserializedOptions'  => ['name' => 'unserializedOptions', 'text' => 'Unserialized Options', 'isExt' => true,],
                'code'      => ['name' => 'code', 'text' => 'Product Code', 'isExt' => true,],
                'code2'     => ['name' => 'code2', 'text' => 'Product Code2', 'isExt' => true,],
                'capacity'  => ['name' => 'capacity', 'text' => 'Product\'s Capacity', 'isExt' => true,],
                'color'     => ['name' => 'color', 'text' => 'Product Color', 'isExt' => true,],
                'interface' => ['name' => 'interface', 'text' => 'Product Interface', 'isExt' => true,],
                'type'      => ['name' => 'type', 'text' => 'Product Type(USB/Accessory/Powerbank/Audio/Service)', 'isExt' => true,],//USB/Accessory/Powerbank/Audio etc.
            ],
        ],
        'accessory' => [//Each attributes value maybe an array
            'name'    => 'accessory',
            'text'    => 'Accessories Of Current Item',
            'attrSet' => [
                //Native Attributes
                'itemIndex'         => ['name' => 'itemIndex', 'text' => 'Item ID',],
                'line'              => ['name' => 'line', 'text' => 'Item\'s Line',],
                'name'              => ['name' => 'name', 'text' => 'Item\'s Full Name',],
                'quantity'          => ['name' => 'quantity', 'text' => 'Item\'s Qty',],
                'serializedOptions' => ['name' => 'serializedOptions', 'text' => 'Serialized Options',],
                'category'          => ['name' => 'category', 'text' => 'Item\'s Category String(Setting In DB)',],
                'options'           => ['name' => 'options', 'text' => 'Item\'s Options',],
                'description'       => ['name' => 'description', 'text' => 'Item\'s Description',],
                'tags'              => ['name' => 'tags', 'text' => 'Item\'s Tags',],
                'difficulty'        => ['name' => 'difficulty', 'text' => 'Item\'s Difficulty',],
                'virtualProof'      => ['name' => 'virtualProof', 'text' => 'Virtual Proof ID',],
                //Extended Attributes
                'unserializedOptions'  => ['name' => 'unserializedOptions', 'text' => 'Unserialized Options', 'isExt' => true,],
                'code'      => ['name' => 'code', 'text' => 'Product Code', 'isExt' => true,],
                'code2'     => ['name' => 'code2', 'text' => 'Product Code2', 'isExt' => true,],
                'capacity'  => ['name' => 'capacity', 'text' => 'Product\'s Capacity', 'isExt' => true,],
                'color'     => ['name' => 'color', 'text' => 'Product Color', 'isExt' => true,],
                'interface' => ['name' => 'interface', 'text' => 'Product Interface', 'isExt' => true,],
                'type'      => ['name' => 'type', 'text' => 'Product Type(USB/Accessory/Powerbank/Audio/Service)', 'isExt' => true,],//USB/Accessory/Powerbank/Audio etc.
            ],
        ],
        'service' => [//Each attributes value maybe an array
            'name'    => 'service',
            'text'    => 'Services Of Current Item',
            'attrSet' => [
                //Native Attributes
                'itemIndex'         => ['name' => 'itemIndex', 'text' => 'Item ID',],
                'line'              => ['name' => 'line', 'text' => 'Item\'s Line',],
                'name'              => ['name' => 'name', 'text' => 'Item\'s Full Name',],
                'quantity'          => ['name' => 'quantity', 'text' => 'Item\'s Qty',],
                'serializedOptions' => ['name' => 'serializedOptions', 'text' => 'Serialized Options',],
                'options'           => ['name' => 'options', 'text' => 'Item\'s Options',],
                'description'       => ['name' => 'description', 'text' => 'Item\'s Description',],
                //Extended Attributes
                'serviceName'        => ['name' => 'serviceName', 'text' => 'Service Name',],
                'custcoldatasize'    => ['name' => 'custcoldatasize', 'text' => 'Data size(Unit: MB)', 'fromJson' => true,],
                'custcoldisksetting' => ['name' => 'custcoldisksetting', 'text' => 'Disk Settings', 'fromJson' => true,],
                'custcolvolumename'  => ['name' => 'custcolvolumename', 'text' => 'Volume Label Name', 'fromJson' => true,],
                'custcolbluetoothname'  => ['name' => 'custcolbluetoothname', 'text' => 'Bluetooth Name', 'fromJson' => true,],
            ],
        ],
        'part' => [
            'name'    => 'part',
            'text'    => 'Current Product Part',
            'attrSet' => [
                //Native Attributes
                'id'           => ['name' => 'id', 'text' => 'Part ID',],
                'name'         => ['name' => 'name', 'text' => 'Part Name',],
                'description'  => ['name' => 'description', 'text' => 'Part Description',],
                'color'        => ['name' => 'color', 'text' => 'Part Color',],
                'capacity'     => ['name' => 'capacity', 'text' => 'Part Capacity(2GB/4GB/2000mAh etc)',],
                'material'     => ['name' => 'material', 'text' => 'Part Material',],
                'card_type'    => ['name' => 'card_type', 'text' => 'Part Card Type Belonged',],
                'secure_qty'   => ['name' => 'secure_qty', 'text' => 'Part Secure Stock',],
                'batch_qty'    => ['name' => 'batch_qty', 'text' => 'Part Batch Qty',],
                'current_qty'  => ['name' => 'current_qty', 'text' => 'Part Current Stock',],
                'comment'      => ['name' => 'comment', 'text' => 'Part\'s Comment',],
                'point'        => ['name' => 'point', 'text' => 'Part\'s Point / Bonus',],
                //Extended Attributes
                //'item_code'    => ['name' => 'item_code', 'text' => 'Part Item Code',],
                'mold'         => ['name' => 'mold', 'text' => 'Part Mold Selected',],
                'construction' => ['name' => 'construction', 'text' => 'Part Memory Type',],
                'memory'       => ['name' => 'memory', 'text' => 'Part Memory Type(Alias of construction)',],
                'item_codes'   => ['name' => 'item_codes', 'text' => 'Model Item Codes Which Consumes The Part',],
                //Maybe array
                'option_names' => ['name' => 'option_names', 'text' => 'Part Option Names',],
                'option_values'=> ['name' => 'option_values', 'text' => 'Part Option Values Set',],
                'option_paired_id' => ['name' => 'option_paired_id', 'text' => 'Part Option\'s Paired ID',],
                'option_lists' => ['name' => 'option_lists', 'text' => 'Part Option Lists Set',],
            ],
        ],
        'craft' => [
            'name'    => 'craft',
            'text'    => 'Craft',
            'attrSet' => [
                'id'   => ['name' => 'id', 'text' => 'Craft ID',],
                'name' => ['name' => 'name', 'text' => 'Craft Name(Internal Identity Name)',],
                'description' => ['name' => 'description', 'text' => 'Craft Description',],
                'display_name' => ['name' => 'display_name', 'text' => 'Craft Display Name',],
            ],
        ],
        'employee' => [
            'name'    => 'employee',
            'text'    => 'Employee',
            'attrSet' => [
                'internalID' => ['name' => 'internalID', 'text' => 'Employee Internal ID',],
                'employeeID' => ['name' => 'employeeID', 'text' => 'Employee ID',],
                'firstName'  => ['name' => 'firstName', 'text' => 'Employee First Name',],
                'lastName'   => ['name' => 'lastName', 'text' => 'Employee Last Name',],
                'jobTitle'   => ['name' => 'jobTitle', 'text' => 'Employee Job Title',],
                'department' => ['name' => 'department', 'text' => 'Employee Department',],
                'departmentID' => ['name' => 'departmentID', 'text' => 'Employee Department ID',],
                'isActive'     => ['name' => 'isActive', 'text' => 'Employee Is Active?',],
            ],
        ],
        'request' => [
            'name'    => 'request',
            'text'    => 'Request',
            'attrSet' => [
                'currentDate' => ['name' => 'currentDate', 'text' => 'Current date',],
                'currentTime' => ['name' => 'currentTime', 'text' => 'Current time',],
                'currentTimestamp' => ['name' => 'currentTimestamp', 'text' => 'Current timestamp',],
                'record' => ['name' => 'record', 'text' => 'Record NO(EM1234/S1234/P1234 etc.)',],
                'action' => ['name' => 'action', 'text' => 'Action(CheckIn/CheckOut/StageComplete)',],
            ],
        ],
    ];

    private $_varValues;
    //
    public function __construct(array $varValues = [])
    {
        $this->_varValues = $varValues;
    }

    static public function isVarValid($masterVar, $secondaryVar = null)
    {
        if(null === $secondaryVar) {
            if(count($t = explode('.', $masterVar)) != 2) {
                return false;
            }
            $masterVar    = $t[0];
            $secondaryVar = $t[1];
        }
        return isset(static :: $_varSet[$masterVar]['attrSet'][$secondaryVar]);
    }

    static public function getVarSetList()
    {
        return static :: $_varSet;
    }

    static public function getMasterVarList()
    {
        return array_keys(static :: $_varSet);
    }

    static public function getSecondaryVarList($masterVar)
    {
        if(!isset(static :: $_varSet[$masterVar]['attrSet'])) {
            throw new \Exception(sprintf('Unrecognized master var: %s', $masterVar));
        }
        return static :: $_varSet[$masterVar]['attrSet'];
    }

    public function setValues($entity, $values)
    {
        if(isset(static :: $_varSet[$entity])) {
            if(!empty($values)) {
                if(!in_array($entity, static :: $_varEntityMulti)) {
                    $values = [$values,];
                }
                foreach($values as $k => $a) {
                    foreach($a as $k => $v) {
                        $this->setValue($entity, $k, $v);
                    }
                }
            }
        }
        return $this;
    }

    public function getValues($entity = null)
    {
        if(null === $entity) {
            return $this->_varValues;
        } elseif(isset($this->_varValues[$entity])) {
            return $this->_varValues[$entity];
        }
        return null;
    }
    /**
     * Set Variable
     *
     * @param string $entity  order/product/part OR product.color/part.mold etc.
     * @param string $property name/code/color OR will be considered as $value when only 2 parameters given
     * @param mixed  $value if default null is used, $property will considered as $value 
     *
     **/
    public function setValue($entity, $property, $value = null, $replaceLast = false)
    {
        if(empty(static :: $_varSet[$entity])
            || empty(static :: $_varSet[$entity]['attrSet'][$property])) {
            //throw new \Exception(sprintf('Unsupported Variable: %s.%s', $entity, $property));
            return $this;
        }
        if(!isset($this->_varValues[$entity])) {
            $this->_varValues[$entity] = [];
        }
        if(in_array($entity, static :: $_varEntityMulti)) {
            if(!array_key_exists($property, $this->_varValues[$entity])) {
                $this->_varValues[$entity][$property] = $value;
            } else {
                $replaceLast = $replaceLast && !empty(static :: $_varSet[$entity]['attrSet'][$property]['isExt']);
                if(!is_array($this->_varValues[$entity][$property])) {
                    if(!$replaceLast) {
                        $this->_varValues[$entity][$property] = [$this->_varValues[$entity][$property],];
                    }
                }
                if($replaceLast) {
                    if(is_array($this->_varValues[$entity][$property])) {
                        foreach($this->_varValues[$entity][$property] as &$v) {}
                        $v = $value;
                        unset($v);
                    } else {
                        $this->_varValues[$entity][$property] = $value;
                    }
                } else {
                    $this->_varValues[$entity][$property][] = $value;
                }
            }
        } else {
            $this->_varValues[$entity][$property] = $value;
        }
        $this->setValueX($entity, $property, $value);
        return $this;
    }

    protected function setValueX($entity, $property, $value)
    {
        $items = OrderCard :: getAllItemsBasicInfo();
        switch($entity) {
        case 'order':
            break;
        case 'customer':
            break;
        case 'item':
        case 'model':
        case 'accessory':
            if('name' == $property) {
                $itemBasicInfo = OrderCard :: getItemAttrByName($value);
                foreach($itemBasicInfo as $key => $val) {
                    $this->setValue($entity, $key, $val, true);
                }
            } elseif('serializedOptions' == $property) {
                $this->setValue($entity, 'unserializedOptions', json_decode($value, true));
            } elseif(in_array($property, ['memory', 'construction',])) {
                    $this->_varValues['item']['memory']      =
                    $this->_varValues['item']['construction']= $value;
            }
            break;
        case 'virtualProof':
            if('pantones' == $property) {
                //$positionSetting = explode(':', rtrim($value, ': '));
                $positionSetting = explode(':', trim($value, ' '));
                //$positions = array_filter($positionSetting);
                $positions = $positionSetting;
                //If pantones is empty, default to 1
                $engravingNum = $value ? 0 : 1;
                $positionEngraving = [];
                $nonEngravingNum   = 0;
                $positionNonEngraving = [];
                $totalColorsNum = 0;
                $positionColors = [];
                $positionColorsNum = [];
                $colorsCounter     = [];
                foreach($positions as $i => $positionColor) {
                    $positionColor = trim($positionColor);
                    $positionEngraving[$i]    = 0;
                    $positionNonEngraving[$i] = 0;
                    if(preg_match('#\b(EMB|END|ENL)\b#i', $positionColor, $matches)) {
                        ++$engravingNum;
                        $positionEngraving[$i] = 1;
                    }
                    if(preg_replace('#\W*(EMB|END|ENL)\W*#i', '', $positionColor)) {
                        ++$nonEngravingNum;
                        $positionNonEngraving[$i] = 1;
                    }
                    $color = preg_replace('#,?\b(EMB|ENL|END|UVP|SPR|SRP)\b,?#i', '', strtoupper($positionColor));
                    $colors= array_filter(preg_split('#\s*,\s*#', $color));
                    $positionColors[$i] = $colors;
                    $positionColorsNum[$i] = count($colors);
                    $totalColorsNum += count($colors);
                    foreach($colors as $_c) {
                        $_c = strtolower($_c);
                        if(!isset($colorsCounter[$_c])) {
                            $colorsCounter[$_c] = 0;
                        }
                        ++$colorsCounter[$_c];
                    }
                }
                $this->setValue('virtualProof', 'positionSetting', $positionSetting)
                     ->setValue('virtualProof', 'engravingNum', $engravingNum)
                     ->setValue('virtualProof', 'positionEngraving', $positionEngraving)
                     ->setValue('virtualProof', 'noEngravingNum', $nonEngravingNum)
                     ->setValue('virtualProof', 'positionNoEngraving', $positionNonEngraving)
                     ->setValue('virtualProof', 'positionNum', count($positions))
                     ->setValue('virtualProof', 'positionColors', $positionColors)
                     ->setValue('virtualProof', 'positionColorsNum', $positionColorsNum)
                     ->setValue('virtualProof', 'colorsNum', $totalColorsNum)
                     ->setValue('virtualProof', 'colorsNumX', array_sum($colorsCounter))
                     ->setValue('virtualProof', 'isUVPantoneColor', $totalColorsNum > 2);
            } elseif('brandingMethod' == $property) {
                $allBrandingMethods = OrderCard :: getBrandingMethods();
                $brandingTxt  = '';
                $brandingArray= [];
                if(!empty($allBrandingMethods[$value]['text'])) {
                    $brandingTxt = $allBrandingMethods[$value]['text'];
                }
                if(!empty($allBrandingMethods[$value]['methods'])) {
                    $brandingArray = $allBrandingMethods[$value]['methods'];
                }
                $this->setValue('virtualProof', 'brandingMethodTxt', $brandingTxt)
                     ->setValue('virtualProof', 'brandingMethodTxtArray', $brandingArray);
            }
            break;
        case 'service':
            if('name' == $property) {
                $this->setValue($entity, 'serviceName', $value, true);
            } elseif('serializedOptions' == $property) {
                if(!empty($value)) {
                    $settings = json_decode($value, true);
                }
                foreach(static :: $_varSet['service']['attrSet'] as $k => $v) {
                    if(empty($v['fromJson']))  continue;
                    if(isset($settings[$k])) {
                        $this->setValue('service', $k, $settings[$k], true);
                    } else {
                        $this->setValue('service', $k, '', true);
                    }
                }
            }
            break;
        case 'part':
            break;
        case 'employee':
            break;
        }
        return $this;
    }
    /**
     * Get Variable's value
     * @See setValue
     * 
     **/
    public function getValue($entity, $property = null)
    {
        if(null === $property) {
            if(count($var = explode('.', $entity)) != 2) {
                throw new \Exception(sprintf('Invalid Var: %s given', $entity));
            }
            list($entity, $property) = $var;
        }
        if(empty(static :: $_varSet[$entity])
            || empty(static :: $_varSet[$entity]['attrSet'][$property])) {
                throw new \Exception(sprintf('Unsupported Variable: %s.%s', $entity, $property));
        }
        if(isset($this->_varValues[$entity][$property])) {
            return $this->_varValues[$entity][$property];
        }
        return null;
    }
}
