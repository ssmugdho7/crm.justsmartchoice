<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Products_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_catalog_gallery_images(array $productIds)
    {
        if (!$productIds || !$this->db->table_exists(db_prefix() . 'product_images')) {
            return [];
        }
        $images = $this->db->where_in('product_id', array_map('intval', $productIds))
            ->order_by('is_primary', 'DESC')->order_by('id', 'ASC')
            ->get(db_prefix() . 'product_images')->result_array();
        $byProduct = [];
        foreach ($images as $image) {
            $byProduct[(int) $image['product_id']][] = $image;
        }
        return $byProduct;
    }

    public function add_product($data)
    {
        $variations = $data['variations'];
        unset($data['variations']);

        $this->db->insert(db_prefix() . 'product_master', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Product Added [ ID:' . $insert_id . ', '. $data['product_name'].', Staff id ' . get_staff_user_id() . ' ]');

            if (isset($variations['variation'])) {
                $variation_count = count($variations['variation']);
                for ($variation_index = 0; $variation_index < $variation_count; $variation_index++) {
                    $this->db->where('name', $variations['variation'][$variation_index]);
                    $variation_row = $this->db->get(db_prefix() . 'variations')->row();
                    if ($variation_row) {
                        $this->db->where('variation_id', $variation_row->id);
                        $this->db->where('value', $variations['variation_value'][$variation_index]);
                        $variation_value_row = $this->db->get(db_prefix() . 'variation_values')->row();
                        if ($variation_value_row) {
                            $product_variation_data = [
                                'product_id' => $insert_id,
                                'variation_id' => $variation_row->id,
                                'variation_value_id' => $variation_value_row->id,
                                'rate' => $variations['rate'][$variation_index],
                                'quantity_number' => $variations['quantity_number'][$variation_index],
                            ];
                            $this->db->insert(db_prefix() . 'product_variations', $product_variation_data);
                        }
                    }
                }
            }

            return $insert_id;
        }

        return false;
    }

    public function get_by_id_product($id = false)
    {
        $this->db->join('product_categories', db_prefix() . 'product_categories.p_category_id=' . db_prefix() . 'product_master.product_category_id', 'LEFT');
        if ($id) {
            $this->db->where_in(db_prefix() . 'product_master.id', $id);
            if (!is_array($id)) {
                $product = $this->db->get(db_prefix() . 'product_master')->row();
                if (!$product) { return $product; }
                $products = $this->attach_product_variations([$product]);
                return $products[0];
            }
            return $this->attach_product_variations($this->db->get(db_prefix() . 'product_master')->result());
        }
        $this->db->order_by(db_prefix() . 'product_master.product_category_id', 'ASC');
        $this->db->order_by(db_prefix() . 'product_master.product_name', 'ASC');
        return $this->attach_product_variations($this->db->get(db_prefix() . 'product_master')->result_array());
    }

    // Fetch options once for the selected products, retaining object/array return formats.
    private function attach_product_variations($products)
    {
        $ids = [];
        foreach ($products as $product) {
            $row = (array) $product;
            if (!empty($row['is_variation'])) { $ids[] = $row['id']; }
        }
        if (!$ids) { return $products; }
        $variations = $this->db
            ->select(db_prefix() . 'product_variations.*, ' . db_prefix() . 'variations.name as variation_name, ' . db_prefix() . 'variation_values.value as variation_value')
            ->join('variations', db_prefix() . 'variations.id=' . db_prefix() . 'product_variations.variation_id', 'LEFT')
            ->join('variation_values', db_prefix() . 'variation_values.id=' . db_prefix() . 'product_variations.variation_value_id', 'LEFT')
            ->where_in('product_id', array_unique($ids))
            ->order_by('variation_id')
            ->get(db_prefix() . 'product_variations')->result();
        $byProduct = [];
        foreach ($variations as $variation) { $byProduct[$variation->product_id][] = $variation; }
        foreach ($products as $index => $product) {
            $row = (array) $product;
            if (empty($row['is_variation'])) { continue; }
            $options = $byProduct[$row['id']] ?? [];
            if (is_object($product)) { $product->variations = $options; }
            else { $products[$index]['variations'] = $options; }
        }
        return $products;
    }

    public function get_populated_category_ids()
    {
        $rows = $this->db->distinct()->select('product_category_id')->get(db_prefix() . 'product_master')->result_array();
        return array_column($rows, 'product_category_id');
    }

	public function get_by_cart_product($cart_data)
	{
		$products = [];
		foreach ($cart_data as $cart_item) {
			$this->db->select('*');
			$this->db->from(db_prefix() . 'product_master');
			$this->db->join('product_categories', db_prefix() . 'product_categories.p_category_id=' . db_prefix() . 'product_master.product_category_id', 'LEFT');
			$this->db->where('id', $cart_item['product_id']);
			$product = $this->db->get()->row();

			if ($product) {
				$product->quantity = $cart_item['quantity'];
				
				// Check if 'product_variation_id' key exists in $cart_item array
				if (!empty($cart_item['product_variation_id'])) {
					$this->db->select(db_prefix() . 'product_variations.*, ' . db_prefix() . 'variations.name as variation_name, ' . db_prefix() . 'variation_values.value as variation_value');
					$this->db->from(db_prefix() . 'product_variations');
					$this->db->join('variations', db_prefix() . 'variations.id=' . db_prefix() . 'product_variations.variation_id', 'LEFT');
					$this->db->join('variation_values', db_prefix() . 'variation_values.id=' . db_prefix() . 'product_variations.variation_value_id', 'LEFT');
					$this->db->where(db_prefix() . 'product_variations.id', $cart_item['product_variation_id']);
					$this->db->where(db_prefix() . 'product_variations.product_id', $cart_item['product_id']);
					$product_variation = $this->db->get()->row();

					if (!$product_variation) { continue; }
					if ($product_variation) {
						$product->product_variation_id = $cart_item['product_variation_id'];
						$product->variation_name = $product_variation->variation_name;
						$product->variation_rate = $product_variation->rate;
						$product->variation_value = $product_variation->variation_value;
						$product->quantity_number = $product_variation->quantity_number;
					}
				}
				
				$products[] = $product;
			} else {
				// Handle case where product is not found or null
				// You might log an error or handle it based on your application's requirements
				log_message('error', 'Product with ID ' . $cart_item['product_id'] . ' not found');
			}
		}

		return $products;
	}

    public function get_by_id_product_afflect_variation($items)
    {
        $products = [];
        foreach ($items as $item) {
            $this->db->join('product_categories', db_prefix() . 'product_categories.p_category_id='.db_prefix() . 'product_master.product_category_id', 'LEFT');
            $this->db->where_in('id', $item['product_id']);
            $product = $this->db->get(db_prefix() . 'product_master')->row();
            if (!$product) { return []; }
            if (!empty($item['product_variation_id'])) {
                $this->db->select(db_prefix() . 'product_variations.*, ' . db_prefix() . 'variations.name as variation_name, ' . db_prefix() . 'variation_values.value as variation_value');
                $this->db->join('variations', db_prefix() . 'variations.id=' . db_prefix() . 'product_variations.variation_id', 'LEFT');
                $this->db->join('variation_values', db_prefix() . 'variation_values.id=' . db_prefix() . 'product_variations.variation_value_id', 'LEFT');
                $this->db->where(db_prefix() . 'product_variations.id', $item['product_variation_id']);
                $this->db->where(db_prefix() . 'product_variations.product_id', $item['product_id']);
                $product_variation = $this->db->get(db_prefix() . 'product_variations')->row();
                if (!$product_variation) { return []; }
                $product->product_name = $product->product_name . ' (' . $product_variation->variation_name . ' ' . $product_variation->variation_value . ' )';
                $product->rate = $product_variation->rate;
                $product->quantity_number = $product_variation->quantity_number;
            }
            $products[] = $product;
        }
        return $products;
    }

    public function get_by_id_variations($id)
    {
        if ($id) {
            $this->db->where('id', $id);
            $product = $this->db->get(db_prefix() . 'product_master')->row();
            $this->db->select(db_prefix() . 'product_variations.*, ' . db_prefix() . 'variations.name as variation_name, ' . db_prefix() . 'variation_values.value as variation_value');
            $this->db->join('variations', db_prefix() . 'variations.id=' . db_prefix() . 'product_variations.variation_id', 'LEFT');
            $this->db->join('variation_values', db_prefix() . 'variation_values.id=' . db_prefix() . 'product_variations.variation_value_id', 'LEFT');
            $this->db->where('product_id', $product->id);
            $this->db->order_by('variation_id');
            $product_variations = $this->db->get(db_prefix() . 'product_variations')->result();
            return $product_variations;
        }

        return [];
    }

    public function get_by_id_variation_values($id, $variation_id = false)
    {
        if ($id) {
            $this->db->where('id', $id);
            $product = $this->db->get(db_prefix() . 'product_master')->row();
            if ($product) {
                $this->db->select(db_prefix() . 'product_variations.*, ' . db_prefix() . 'variations.name as variation_name, ' . db_prefix() . 'variation_values.value as variation_value');
                $this->db->join('variations', db_prefix() . 'variations.id=' . db_prefix() . 'product_variations.variation_id', 'LEFT');
                $this->db->join('variation_values', db_prefix() . 'variation_values.id=' . db_prefix() . 'product_variations.variation_value_id', 'LEFT');
                $this->db->where(db_prefix() . 'product_variations.product_id', $product->id);
                if ($variation_id) {
                    $this->db->where(db_prefix() . 'product_variations.variation_id', $variation_id);
                } else {
                    $this->db->order_by('variation_id');
                }
                $product_variations = $this->db->get(db_prefix() . 'product_variations')->result();
                return $product_variations;
            }
        }

        return [];
    }

    public function get_category_filter($p_category_id)
    {
        if (!empty($p_category_id)) {
            if (!is_array($p_category_id)) {
                $p_category_id = [$p_category_id];
            }
            $this->db->where_in(db_prefix() . 'product_master.product_category_id', $p_category_id);
        }
        $this->db->order_by(db_prefix() . 'product_master.product_category_id', 'ASC');
        $this->db->order_by(db_prefix() . 'product_master.product_name', 'ASC');

        return $this->get_by_id_product();
    }

    public function edit_product($data, $id)
    {
        $variations = [];
        if (isset($data['variations'])) {
            $variations = $data['variations'];
            unset($data['variations']);
        }

        $product = $this->get_by_id_product($id);
        $this->db->where('id', $id);
        $res = $this->db->update(db_prefix() . 'product_master', $data);
        if ($this->db->affected_rows() > 0) {
            if (!empty($data['quantity_number']) && $product->quantity_number != $data['quantity_number']) {
                log_activity('Product Quantity updated[ ID: '.$id.', From: '.$product->quantity_number.' To: '.$data['quantity_number'].' Staff id '.get_staff_user_id().']');
            }
            log_activity('Product Details updated[ ID: '.$id.', '.$product->product_name.', Staff id '.get_staff_user_id().' ]');
        }

        if (isset($variations['variation'])) {
            $variation_count = count($variations['variation']);
            for ($variation_index = 0; $variation_index < $variation_count; $variation_index++) {
                $this->db->where('name', $variations['variation'][$variation_index]);
                $variation_row = $this->db->get(db_prefix() . 'variations')->row();
                if ($variation_row) {
                    $this->db->where('variation_id', $variation_row->id);
                    $this->db->where('value', $variations['variation_value'][$variation_index]);
                    $variation_value_row = $this->db->get(db_prefix() . 'variation_values')->row();
                    if ($variation_value_row) {
                        $this->db->where('product_id', $id);
                        $this->db->where('variation_id', $variation_row->id);
                        $this->db->where('variation_value_id', $variation_value_row->id);
                        $product_variation_row = $this->db->get(db_prefix() . 'product_variations')->row();
                        if ($product_variation_row) {
                            $product_variation_data = [
                                'rate' => $variations['rate'][$variation_index],
                                'quantity_number' => $variations['quantity_number'][$variation_index],
                            ];
                            $this->db->where('id', $product_variation_row->id);
                            $this->db->update(db_prefix() . 'product_variations', $product_variation_data);
                            if ($this->db->affected_rows() > 0) {
                                log_activity('Product Variation Details Updated [ ID: ' . $product_variation_row->id . ', ' . $variation_row->name . ', ' . $variation_value_row->value . ' ]');
                            }
                        } else {
                            $product_variation_data = [
                                'product_id' => $id,
                                'variation_id' => $variation_row->id,
                                'variation_value_id' => $variation_value_row->id,
                                'rate' => $variations['rate'][$variation_index],
                                'quantity_number' => $variations['quantity_number'][$variation_index],
                            ];
                            $this->db->insert(db_prefix() . 'product_variations', $product_variation_data);
                            $insert_id = $this->db->insert_id();
                            log_activity('Product Variation Details Added [ ID: ' . $insert_id . ', ' . $variation_row->name . ', ' . $variation_value_row->value . ' ]');
                        }
                    }
                }
            }
            
            $this->db->where('product_id', $id);
            $product_variations = $this->db->get(db_prefix() . 'product_variations')->result_array();
            foreach ($product_variations as $product_variation) {
                $product_variation_exist = false;
                $this->db->where('id', $product_variation['variation_id']);
                $variation_row = $this->db->get(db_prefix() . 'variations')->row();
                $this->db->where('id', $product_variation['variation_value_id']);
                $variation_value_row = $this->db->get(db_prefix() . 'variation_values')->row();
                if ($variation_row && $variation_value_row) {
                    $variation_count = count($variations['variation']);
                    for ($variation_index = 0; $variation_index < $variation_count; $variation_index++) {
                        if ($variation_row->name == $variations['variation'][$variation_index] && $variation_value_row->value == $variations['variation_value'][$variation_index]) {
                            $product_variation_exist = true;
                            break;
                        }
                    }
                }
                if (!$product_variation_exist) {
                    $this->db->where('id', $product_variation['id']);
                    $this->db->delete(db_prefix() . 'product_variations');
                    log_activity('Product Variation Details Deleted [ ID: ' . $product_variation['id'] . ' ]');
                }
            }
        }

        if ($res) {
            return true;
        }

        return false;
    }

    public function delete_by_id_product($id)
    {
        $product  = $this->get_by_id_product($id);
        $relPath  = get_upload_path_by_type('products').'/';
        $fullPath = $relPath.$product->product_image;
        unlink($fullPath);
        if (!empty($id)) {
            $this->db->where('id', $id);
        }
        $result = $this->db->delete(db_prefix() . 'product_master');
        log_activity('Product Deleted[ ID: '.$id.', '.$product->product_name.', Staff id '.get_staff_user_id().' ]');

        $this->db->where('product_id', $id);
        $product_variations = $this->db->get(db_prefix() . 'product_variations')->result_array();
        foreach ($product_variations as $product_variation) {
            $this->db->where('id', $product_variation['id']);
            $this->db->delete(db_prefix() . 'product_variations');
            log_activity('Product Variation Details Deleted [ ID: ' . $product_variation['id'] . ' ]');
        }

        return $result;
    }
}
