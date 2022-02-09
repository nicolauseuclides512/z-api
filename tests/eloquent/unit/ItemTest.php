<?php

use Laravel\Lumen\Testing\WithoutMiddleware;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class ItemTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * A unit testing .
     *
     * @return void
     */

    public function test_required_it_rejects_organization_id_if_it_is_empty()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->organization_id = '';
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'organization_id'), false);
    }

    public function test_required_it_rejects_organization_id_if_it_is_null()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->organization_id = null;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'organization_id'), false);
    }

    public function test_integer_it_rejects_organization_id_if_it_is_alphabet()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->organization_id = 'A';
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'organization_id'), false);
    }

    public function test_exist_it_rejects_organization_id_if_hook_not_exist()
    {
        #prepare data
        $organization = App\Models\Organization::destroy(1000);
        $item = factory('App\Models\Item')->make();
        $item->organization_id = 1000;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'organization_id'), false);
    }

    public function test_required_it_rejects_user_id_if_it_is_empty()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->user_id = '';
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'user_id'), false);
    }

    public function test_required_it_rejects_user_id_if_it_is_null()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->user_id = null;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'user_id'), false);
    }

    public function test_integer_it_rejects_user_id_if_it_is_alphabet()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->user_id = 'A';
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'user_id'), false);
    }

    public function test_exist_it_rejects_user_id_if_hook_not_exist()
    {
        #prepare data
        $organization = App\Models\Organization::destroy(1000);
        $item = factory('App\Models\Item')->make();
        $item->user_id = 1000;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'user_id'), false);
    }

    public function test_required_it_accepts_itemgroup_id_if_it_is_null()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->itemgroup_id = null;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'itemgroup_id'), true);
    }

    public function test_integer_it_rejects_itemgroup_id_if_it_is_alphabet()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->itemgroup_id = 'A';
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'itemgroup_id'), false);
    }

    public function test_exist_it_rejects_itemgroup_id_if_hook_not_exist()
    {
        #prepare data
        $item_group = App\Models\Item::destroy(1000);
        $item = factory('App\Models\Item')->make();
        $item->itemgroup_id = 1000;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'itemgroup_id'), false);
    }

    public function test_required_it_rejects_uom_id_if_it_is_empty()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->uom_id = '';
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'uom_id'), false);
    }

    public function test_required_it_rejects_uom_id_if_it_is_null()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->uom_id = null;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'uom_id'), false);
    }

    public function test_integer_it_rejects_uom_id_if_it_is_alphabet()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->uom_id = 'A';
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'uom_id'), false);
    }

    public function test_exist_it_rejects_uom_id_if_hook_not_exist()
    {
        #prepare data
        $uom = App\Models\AssetUom::destroy(1000);
        $item = factory('App\Models\Item')->make();
        $item->uom_id = 1000;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'uom_id'), false);
    }

    public function test_required_it_accepts_tax_id_if_it_is_null()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->tax_id = null;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'tax_id'), true);
    }

    public function test_integer_it_rejects_tax_id_if_it_is_alphabet()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->tax_id = 'A';
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'tax_id'), false);
    }

    public function test_exist_it_rejects_tax_id_if_hook_not_exist()
    {
        #prepare data
        $tax = App\Models\AssetTax::destroy(1000);
        $item = factory('App\Models\Item')->make();
        $item->tax_id = 1000;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'tax_id'), false);
    }

    public function test_max_it_rejects_item_name_if_it_is_greater_than_max()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->item_name = str_repeat('A', 101);
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'item_name'), false);
    }

    public function test_required_it_rejects_item_name_if_it_is_empty()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->item_name = '';
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'item_name'), false);
    }

    public function test_required_it_rejects_item_name_if_it_is_null()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->item_name = null;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'item_name'), false);
    }

    #TODO: attribute type, attribute_options

    public function test_required_it_rejects_weight_if_it_is_empty()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->weight = '';
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'weight'), false);
    }

    public function test_required_it_rejects_weight_if_it_is_null()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->weight = null;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'weight'), false);
    }

    public function test_numeric_it_rejects_weight_if_it_is_alphabet()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->weight = 'A';
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'weight'), false);
    }

    public function test_numeric_it_rejects_weight_if_it_is_negative_number()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->weight = -10;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'weight'), false);
    }

    public function test_numeric_it_rejects_weight_if_it_is_zero()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->weight = 0;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'weight'), false);
    }

    public function test_required_it_accepts_dimension_p_if_it_is_empty()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->dimension_p = '';
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'dimension_p'), true);
    }

    public function test_required_it_accepts_dimension_p_if_it_is_null()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->dimension_p = null;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'dimension_p'), true);
    }

    public function test_numeric_it_rejects_dimension_p_if_it_is_alphabet()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->dimension_p = 'A';
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'dimension_p'), false);
    }

    public function test_numeric_it_rejects_dimension_p_if_it_is_negative_number()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->dimension_p = -10;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'dimension_p'), false);
    }

    public function test_numeric_it_rejects_dimension_p_if_it_is_zero()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->dimension_p = 0;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'dimension_p'), false);
    }

    public function test_required_it_accepts_dimension_l_if_it_is_empty()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->dimension_l = '';
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'dimension_l'), true);
    }

    public function test_required_it_accepts_dimension_l_if_it_is_null()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->dimension_l = null;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'dimension_l'), true);
    }

    public function test_numeric_it_rejects_dimension_l_if_it_is_alphabet()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->dimension_l = 'A';
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'dimension_l'), false);
    }

    public function test_numeric_it_rejects_dimension_l_if_it_is_negative_number()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->dimension_l = -10;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'dimension_l'), false);
    }

    public function test_numeric_it_rejects_dimension_l_if_it_is_zero()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->dimension_l = 0;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'dimension_l'), false);
    }

    public function test_required_it_accepts_dimension_t_if_it_is_empty()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->dimension_t = '';
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'dimension_t'), true);
    }

    public function test_required_it_accepts_dimension_t_if_it_is_null()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->dimension_t = null;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'dimension_t'), true);
    }

    public function test_numeric_it_rejects_dimension_t_if_it_is_alphabet()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->dimension_t = 'A';
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'dimension_t'), false);
    }

    public function test_numeric_it_rejects_dimension_t_if_it_is_negative_number()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->dimension_t = -10;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'dimension_t'), false);
    }

    public function test_numeric_it_rejects_dimension_t_if_it_is_zero()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->dimension_t = 0;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'dimension_t'), false);
    }

    public function test_max_it_rejects_code_sku_if_it_is_greater_than_max()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->code_sku = str_repeat('A', 16);
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'code_sku'), false);
    }

    public function test_required_it_rejects_code_sku_if_it_is_empty()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->code_sku = '';
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'code_sku'), false);
    }

    public function test_required_it_rejects_code_sku_if_it_is_null()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->code_sku = null;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'code_sku'), false);
    }

    public function test_max_it_rejects_code_ean_if_it_is_greater_than_max()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->code_ean = str_repeat('A', 16);
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'code_ean'), false);
    }

    public function test_required_it_accepts_code_ean_if_it_is_empty()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->code_ean = '';
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'code_ean'), true);
    }

    public function test_required_it_accepts_code_ean_if_it_is_null()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->code_ean = null;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'code_ean'), true);
    }

    public function test_max_it_rejects_code_upc_if_it_is_greater_than_max()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->code_upc = str_repeat('A', 16);
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'code_upc'), false);
    }

    public function test_required_it_accepts_code_upc_if_it_is_empty()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->code_upc = '';
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'code_upc'), true);
    }

    public function test_required_it_accepts_code_upc_if_it_is_null()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->code_upc = null;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'code_upc'), true);
    }

    public function test_max_it_rejects_code_mpn_if_it_is_greater_than_max()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->code_mpn = str_repeat('A', 16);
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'code_mpn'), false);
    }

    public function test_required_it_accepts_code_mpn_if_it_is_empty()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->code_mpn = '';
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'code_mpn'), true);
    }

    public function test_required_it_accepts_code_mpn_if_it_is_null()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->code_mpn = null;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'code_mpn'), true);
    }

    public function test_max_it_rejects_code_isbn_if_it_is_greater_than_max()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->code_isbn = str_repeat('A', 16);
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'code_isbn'), false);
    }

    public function test_required_it_accepts_code_isbn_if_it_is_empty()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->code_isbn = '';
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'code_isbn'), true);
    }

    public function test_required_it_accepts_code_isbn_if_it_is_null()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->code_isbn = null;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'code_isbn'), true);
    }

    public function test_required_it_accepts_sales_checked_if_it_is_empty()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->sales_checked = '';
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'sales_checked'), true);
    }

    public function test_required_it_accepts_sales_checked_if_it_is_null()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->sales_checked = null;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'sales_checked'), true);
    }

    public function test_integer_it_rejects_sales_checked_if_it_is_not_integer()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->sales_checked = 'A';
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'sales_checked'), false);
    }

    public function test_inclusion_it_rejects_sales_checked_if_it_is_not_in_list()
    {
        #prepare data
        $sales_checked_values = array(-2, -1, 2, 3, 4);

        foreach ($sales_checked_values as $value) {
            $item = factory('App\Models\Item')->make();
            $item->sales_checked = $value;
            $item->save();

            #checking error message
            $this->assertEquals($this->isValidationPass($item, 'sales_checked'), false);
        }
    }

    public function test_numeric_it_rejects_sales_rate_if_it_is_alphabet()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->sales_rate = 'A';
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'sales_rate'), false);
    }

    public function test_numeric_it_rejects_sales_rate_if_it_is_negative_number()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->sales_rate = -10;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'sales_rate'), false);
    }

    public function test_numeric_it_rejects_sales_rate_if_it_is_zero()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->sales_rate = 0;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'sales_rate'), false);
    }

    public function test_numeric_it_accepts_sales_rate_if_it_is_a_float()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->sales_rate = 5.55;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'sales_rate'), true);
    }

    public function test_required_it_requires_sales_rate_if_sales_checked_is_true()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->sales_rate = null;
        $item->sales_checked = 1;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'sales_rate'), false);
    }

    public function test_required_it_accepts_empty_sales_rate_if_sales_checked_is_false()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->sales_rate = null;
        $item->sales_checked = 0;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'sales_rate'), true);
    }

    public function test_max_it_rejects_sales_description_if_it_is_greater_than_max()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->sales_description = str_repeat('A', 501);
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'sales_description'), false);
    }

    public function test_numeric_it_rejects_sales_account_if_it_is_alphabet()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->sales_account = 'A';
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'sales_account'), false);
    }


    public function test_required_it_requires_sales_account_if_sales_checked_is_true()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->sales_account = null;
        $item->sales_checked = 1;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'sales_account'), false);
    }

    public function test_required_it_accepts_empty_sales_account_if_sales_checked_is_false()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->sales_account = null;
        $item->sales_checked = 0;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'sales_account'), true);
    }

    public function test_exist_it_rejects_sales_account_if_hook_not_exist()
    {
        #prepare data
        $non_existing_sa = App\Models\AssetAccount::destroy(100);

        $item = factory('App\Models\Item')->make();
        $item->sales_checked = 1;
        $item->sales_account = 100;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'sales_account'), false);
    }

    public function test_inclusion_it_rejects_purchase_checked_if_it_is_not_in_list()
    {
        #prepare data
        $purchase_checked_values = array(-2, -1, 2, 3, 4);

        foreach ($purchase_checked_values as $value) {
            $item = factory('App\Models\Item')->make();
            $item->purchase_checked = $value;
            $item->save();

            #checking error message
            $this->assertEquals($this->isValidationPass($item, 'purchase_checked'), false);
        }
    }

    public function test_numeric_it_rejects_purchase_rate_if_it_is_alphabet()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->purchase_rate = 'A';
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'purchase_rate'), false);
    }

    public function test_numeric_it_rejects_purchase_rate_if_it_is_negative_number()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->purchase_rate = -10;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'purchase_rate'), false);
    }

    public function test_numeric_it_rejects_purchase_rate_if_it_is_zero()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->purchase_rate = 0;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'purchase_rate'), false);
    }

    public function test_numeric_it_accepts_purchase_rate_if_it_is_a_float()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->purchase_rate = 5.55;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'purchase_rate'), true);
    }

    public function test_required_it_requires_purchase_rate_if_purchase_checked_is_true()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->purchase_rate = null;
        $item->purchase_checked = 1;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'purchase_rate'), false);
    }

    public function test_required_it_accepts_empty_purchase_rate_if_sales_checked_is_false()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->purchase_rate = null;
        $item->sales_checked = 0;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'purchase_rate'), true);
    }

    public function test_max_it_rejects_purchase_description_if_it_is_greater_than_max()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->purchase_description = str_repeat('A', 501);
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'purchase_description'), false);
    }

    public function test_numeric_it_rejects_purchase_account_if_it_is_alphabet()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->purchase_account = 'A';
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'purchase_account'), false);
    }


    public function test_required_it_requires_purchase_account_if_purchase_checked_is_true()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->purchase_account = null;
        $item->purchase_checked = 1;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'purchase_account'), false);
    }

    public function test_required_it_accepts_empty_purchase_account_if_sales_checked_is_false()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->purchase_account = null;
        $item->sales_checked = 0;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'purchase_account'), true);
    }

    public function test_exist_it_rejects_purchase_account_if_hook_not_exist()
    {
        #prepare data
        $non_existing_sa = App\Models\AssetAccount::destroy(100);

        $item = factory('App\Models\Item')->make();
        $item->sales_checked = 1;
        $item->purchase_account = 100;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'purchase_account'), false);
    }

    public function test_required_it_accepts_preferred_vendor_if_it_is_null()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->preferred_vendor = null;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'preferred_vendor'), true);
    }

    public function test_integer_it_rejects_preferred_vendor_if_it_is_alphabet()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->preferred_vendor = 'A';
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'preferred_vendor'), false);
    }

    public function test_exist_it_rejects_preferred_vendor_if_hook_not_exist()
    {
        #prepare data
        $contact = App\Models\Contact::destroy(1000);
        $item = factory('App\Models\Item')->make();
        $item->preferred_vendor = 1000;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'preferred_vendor'), false);
    }

    public function test_required_it_rejects_item_status_if_it_is_empty()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->item_status = '';
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'item_status'), false);
    }

    public function test_required_it_rejects_item_status_if_it_is_null()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->item_status = null;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'item_status'), false);
    }

    public function test_integer_it_rejects_item_status_if_it_is_not_integer()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->item_status = 'A';
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'item_status'), false);
    }

    public function test_inclusion_it_rejects_item_status_if_it_is_not_in_list()
    {
        #prepare data
        $item_statuses = array(-1, 2, 3, 4);

        foreach ($item_statuses as $status) {
            $item = factory('App\Models\Item')->make();
            $item->item_status = $status;
            $item->save();

            #checking error message
            $this->assertEquals($this->isValidationPass($item, 'item_status'), false);
        }
    }

    //TODO: attribute_type, attribute options

    public function test_required_it_accepts_inventory_checked_if_it_is_empty()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->inventory_checked = '';
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'inventory_checked'), true);
    }

    public function test_required_it_accepts_inventory_checked_if_it_is_null()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->inventory_checked = null;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'inventory_checked'), true);
    }

    public function test_integer_it_rejects_inventory_checked_if_it_is_not_integer()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->inventory_checked = 'A';
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'inventory_checked'), false);
    }

    public function test_inclusion_it_rejects_inventory_checked_if_it_is_not_in_list()
    {
        #prepare data
        $inventory_checked_values = array(-2, -1, 2, 3, 4);

        foreach ($inventory_checked_values as $value) {
            $item = factory('App\Models\Item')->make();
            $item->inventory_checked = $value;
            $item->save();

            #checking error message
            $this->assertEquals($this->isValidationPass($item, 'inventory_checked'), false);
        }
    }

    public function test_numeric_it_rejects_inventory_rate_if_it_is_alphabet()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->inventory_rate = 'A';
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'inventory_rate'), false);
    }

    public function test_numeric_it_rejects_inventory_rate_if_it_is_negative_number()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->inventory_rate = -10;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'inventory_rate'), false);
    }

    public function test_numeric_it_accepts_inventory_rate_if_it_is_zero()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->inventory_rate = 0;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'inventory_rate'), true);
    }

    public function test_numeric_it_accepts_inventory_rate_if_it_is_a_float()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->inventory_rate = 5.55;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'inventory_rate'), true);
    }

    public function test_required_it_accepts_inventory_rate_if_it_is_null()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->inventory_rate = null;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'inventory_rate'), true);
    }

    public function test_integer_it_rejects_inventory_stock_if_it_is_not_positive_integer()
    {
        $inventory_stock_values = array(-2, -1, 'A');

        foreach ($inventory_stock_values as $value) {
            #prepare data
            $item = factory('App\Models\Item')->make();
            $item->inventory_stock = $value;
            $item->save();

            #checking error message
            $this->assertEquals($this->isValidationPass($item, 'inventory_stock'), false);
        }
    }

    public function test_numeric_it_rejects_inventory_account_f_it_is_alphabet()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->inventory_account = 'A';
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'inventory_account'), false);
    }

    public function test_required_it_requires_inventory_account_if_inventory_checked_is_true()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->inventory_account = null;
        $item->inventory_checked = 1;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'inventory_account'), false);
    }

    public function test_required_it_accepts_empty_inventory_account_f_inventory_checked_is_false()
    {
        #prepare data
        $item = factory('App\Models\Item')->make();
        $item->inventory_account = null;
        $item->inventory_checked = 0;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'inventory_account'), true);
    }

    public function test_exist_it_rejects_inventory_account_if_hook_not_exist()
    {
        #prepare data
        $non_existing_sa = App\Models\AssetAccount::destroy(100);

        $item = factory('App\Models\Item')->make();
        $item->sales_checked = 1;
        $item->inventory_account = 100;
        $item->save();

        #checking error message
        $this->assertEquals($this->isValidationPass($item, 'inventory_account'), false);
    }

    public function test_it_saves_valid_item_object()
    {
        #prepare data
        $default_country = factory('App\Models\AssetCountry', 'default_country')->create();
        $default_province = factory('App\Models\AssetProvince', 'default_province')->create();
        $default_district = factory('App\Models\AssetDistrict', 'default_district')->create();
        $default_region = factory('App\Models\AssetRegion', 'default_region')->create();
        $default_currency = factory('App\Models\AssetCurrency', 'default_currency')->create();
        $default_timezone = factory('App\Models\AssetTimezone', 'default_timezone')->create();

        $default_organization = factory('App\Models\Organization', 'default_organization')->make();
        $default_organization->country_id = $default_country->country_id;
        $default_organization->province_id = $default_province->province_id;
        $default_organization->district_id = $default_district->district_id;
        $default_organization->region_id = $default_region->region_id;
        $default_organization->timezone_id = $default_timezone->timezone_id;
        $default_organization->save();

        $default_user = factory('App\Models\User', 'default_user')->create();
        $default_uom = factory('App\Models\AssetUom', 'default_uom')->create();

        $item = factory('App\Models\Item')->make();
        $item->organization_id = $default_organization->organization_id;
        $item->user_id = $default_user->user_id;
        $item->uom_id = $default_uom->uom_id;
        unset($item->tax_id, $item->preferred_vendor);

        #checking error message
        $this->assertEquals($item->save(), true);
    }

    public function test_import_mass_from_excel_it_can_be_created()
    {

    }

    public function test_export_mass_from_array_to_excel_it_must_be_downloaded()
    {
        \App\Models\Item::inst()->exportMass();
    }
}