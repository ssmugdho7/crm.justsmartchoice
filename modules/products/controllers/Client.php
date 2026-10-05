<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
class Client extends ClientsController
{
    public function __construct()
    {
        parent::__construct();
    }



    public function cart()
    {
        return $this->place_order();
    }

    public function my_cart()
    {
        $ids = $this->input->get('id');
        foreach (is_array($ids) ? $ids : [$ids] as $product_id) {
            $this->add_shortcut_item($product_id);
        }
        redirect('products/client/place_order');
    }

    public function manualorder()
    {
        $this->add_shortcut_item($this->input->get('id'));
        redirect('products/client/place_order');
    }

    private function add_shortcut_item($product_id)
    {
        if (!is_scalar($product_id) || !ctype_digit((string)$product_id) || (int)$product_id < 1) {
            return;
        }
        $cart = $this->session->userdata('cart_data');
        $cart = is_array($cart) ? $cart : [];
        foreach ($cart as $index => $item) {
            if ($item['product_id'] == $product_id && empty($item['product_variation_id'])) {
                $cart[$index]['quantity'] = max(1, (int)($item['quantity'] ?? 0)) + 1;
                $cart[$index]['product_variation_id'] = '';
                $this->session->set_userdata('cart_data', array_values($cart));
                return;
            }
        }
        $cart[] = ['product_id' => $product_id, 'product_variation_id' => '', 'quantity' => 1];
        $this->session->set_userdata('cart_data', array_values($cart));
    }


    public function get_my_cart()
    {
        echo json_encode(($this->session->userdata('cart_data') ?? []));
    }

    private function get_cart_product($product_id)
    {
        $cart_data     = ($this->session->userdata('cart_data') ?? []);
        if (!empty($cart_data)) {
            foreach ($cart_data as $cart_item) {
                if ($cart_item['product_id'] == $product_id) {
                    return $cart_item;
                }
            }
        }

        return [];
    }

    private function get_cart_product_ids()
    {
        $cart_data     = ($this->session->userdata('cart_data') ?? []);
        $cart_product_ids = [];
        if (!empty($cart_data)) {
            foreach ($cart_data as $cart_item) {
                $cart_product_ids[] = $cart_item['product_id'];
            }
        }

        return $cart_product_ids;
    }


    private function sc_catalog_language()
    {
        $language = '';
        if (is_client_logged_in() && function_exists('get_contact')) {
            $contact = get_contact();
            if (!empty($contact->default_language)) {
                $language = strtolower($contact->default_language);
            }
        }
        if (empty($language) && $this->session->userdata('language')) {
            $language = strtolower($this->session->userdata('language'));
        }
        if (empty($language) && !empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $language = strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));
        }
        return (strpos($language, 'spanish') !== false || strpos($language, 'es') === 0) ? 'spanish' : 'english';
    }

    private function sc_catalog_translations()
    {
        return [
            'categories' => [
                'Flooring' => 'Pisos', 'Doors' => 'Puertas', 'Windows' => 'Ventanas', 'Bathrooms' => 'Baños', 'Kitchen' => 'Cocina', 'Painting' => 'Pintura', 'Drywall' => 'Drywall', 'Electrical' => 'Electricidad', 'Plumbing' => 'Plomería', 'HVAC' => 'Aire acondicionado', 'Roofing' => 'Techos', 'Concrete' => 'Concreto', 'Fencing' => 'Cercas', 'Engineering & Permits' => 'Ingeniería y permisos', 'Design Services' => 'Servicios de diseño', 'Cleaning' => 'Limpieza', 'Miscellaneous' => 'Misceláneos'
            ],
            'variations' => [
                'Door Width' => 'Ancho de puerta', 'Door Height' => 'Altura de puerta', 'Door Type' => 'Tipo de puerta', 'Floor Level' => 'Nivel del piso', 'Old Door Removal' => 'Remover puerta vieja', 'Trim Option' => 'Opción de moldura', 'Paint Option' => 'Opción de pintura',
                'Window Count' => 'Cantidad de ventanas', 'Window Type' => 'Tipo de ventana', 'Window Size' => 'Tamaño de ventana', 'Old Window Disposal' => 'Botar ventana vieja', 'Cleaning Scope' => 'Alcance de limpieza', 'Screen Cleaning' => 'Limpieza de screen', 'Track Cleaning' => 'Limpieza de rieles', 'Hard Water Removal' => 'Remover manchas de agua',
                'Fence Height' => 'Altura de cerca', 'Fence Material' => 'Material de cerca', 'Gate Option' => 'Opción de portón', 'Double Gate Option' => 'Opción de portón doble', 'Remove Existing Fence' => 'Remover cerca existente', 'Permit Help' => 'Ayuda con permiso', 'HOA Help' => 'Ayuda con HOA',
                'Engineering Tier' => 'Nivel de ingeniería', 'Project Type' => 'Tipo de proyecto', 'Plan Type' => 'Tipo de plano', 'Delivery' => 'Entrega',
                'Removal' => 'Remoción', 'Furniture Moving' => 'Mover muebles', 'Stairs' => 'Escaleras', 'Baseboard Option' => 'Opción de baseboard', 'Moisture Barrier' => 'Barrera de humedad', 'Pattern' => 'Patrón',
                'Painting Scope' => 'Alcance de pintura', 'Area' => 'Área', 'Texture Repair' => 'Reparación de textura', 'Primer' => 'Primer', 'Paint Grade' => 'Calidad de pintura',
                'Roof Type' => 'Tipo de techo', 'Tear Off' => 'Remoción de techo', 'Dumpster' => 'Dumpster', 'Story Level' => 'Nivel de piso',
                'Electrical Service' => 'Servicio eléctrico', 'Permit Required' => 'Permiso requerido', 'Fixture Type' => 'Tipo de fixture', 'Circuit Type' => 'Tipo de circuito',
                'Plumbing Service' => 'Servicio de plomería', 'Water Heater Type' => 'Tipo de calentador', 'Inspection Option' => 'Opción de inspección',
                'Design Package' => 'Paquete de diseño', 'Room Count' => 'Cantidad de cuartos', 'Cleaning Level' => 'Nivel de limpieza'
            ],
            'values' => [
                'Yes' => 'Sí', 'No' => 'No', 'None' => 'Ninguno', 'First Floor' => 'Primer piso', 'Second Floor' => 'Segundo piso', 'Third Floor' => 'Tercer piso', '24 in' => '24 pulg.', '28 in' => '28 pulg.', '30 in' => '30 pulg.', '32 in' => '32 pulg.', '36 in' => '36 pulg.', '80 in' => '80 pulg.', '84 in' => '84 pulg.', '96 in' => '96 pulg.', 'Hollow Core' => 'Hueca', 'Solid Core' => 'Sólida', 'Fire Rated' => 'Contra fuego', 'Existing Trim' => 'Moldura existente', 'New Trim' => 'Moldura nueva', 'Paint Door' => 'Pintar puerta', 'Unpainted' => 'Sin pintar',
                '1-10 Windows' => '1-10 ventanas', '11-20 Windows' => '11-20 ventanas', '21-30 Windows' => '21-30 ventanas', '31-40 Windows' => '31-40 ventanas', '40+ Windows' => '40+ ventanas', 'Single Hung' => 'Single hung', 'Double Hung' => 'Double hung', 'Slider' => 'Corrediza', 'Impact' => 'Impacto', 'Standard' => 'Estándar', 'Small' => 'Pequeña', 'Medium' => 'Mediana', 'Large' => 'Grande', 'Haul Away Old Window' => 'Botar ventana vieja', 'Leave On Site' => 'Dejar en sitio', 'Inside Only' => 'Interior solamente', 'Outside Only' => 'Exterior solamente', 'Inside and Outside' => 'Interior y exterior', 'Screens Included' => 'Screens incluidos', 'Tracks Included' => 'Rieles incluidos', 'Hard Water Treatment' => 'Tratamiento de manchas de agua',
                '4 ft' => '4 pies', '6 ft' => '6 pies', '8 ft' => '8 pies', 'Wood' => 'Madera', 'Vinyl' => 'Vinilo', 'Aluminum' => 'Aluminio', 'Chain Link' => 'Cyclone', 'No Gate' => 'Sin portón', '48 in Gate' => 'Portón 48 pulg.', '60 in Gate' => 'Portón 60 pulg.', '72 in Gate' => 'Portón 72 pulg.', '8 ft Double Gate' => 'Portón doble 8 pies', '10 ft Double Gate' => 'Portón doble 10 pies', '12 ft Double Gate' => 'Portón doble 12 pies',
                'Up to $2,500' => 'Hasta $2,500', '$2,500-$5,000' => '$2,500-$5,000', '$5,000-$10,000' => '$5,000-$10,000', '$10,000+' => '$10,000+', 'Residential' => 'Residencial', 'Commercial' => 'Comercial', 'Structural' => 'Estructural', 'MEP' => 'MEP', 'Energy Calculation' => 'Cálculo energético', 'Wind Load' => 'Carga de viento', 'Digital Signature' => 'Firma digital', 'Permit Package' => 'Paquete de permiso',
                'Existing Floor Removal' => 'Remover piso existente', 'No Removal' => 'Sin remoción', 'Furniture Move Included' => 'Mover muebles incluido', 'Straight Lay' => 'Instalación recta', 'Diagonal Pattern' => 'Patrón diagonal', 'Herringbone' => 'Herringbone', 'Quarter Round' => 'Quarter round', 'New Baseboards' => 'Baseboards nuevos',
                'Interior' => 'Interior', 'Exterior' => 'Exterior', 'Walls' => 'Paredes', 'Ceilings' => 'Cielos rasos', 'Trim and Doors' => 'Molduras y puertas', 'Minor Texture Repair' => 'Reparación menor de textura', 'Premium Paint' => 'Pintura premium',
                'Shingle' => 'Shingle', 'Metal' => 'Metal', 'Flat Roof' => 'Techo plano', 'One Story' => 'Un piso', 'Two Story' => 'Dos pisos', 'Panel Upgrade' => 'Cambio de panel', 'New Circuit' => 'Circuito nuevo', 'EV Charger' => 'Cargador EV', 'Ceiling Fan' => 'Abanico de techo', 'Outlet or Switch' => 'Outlet o switch', 'Toilet' => 'Inodoro', 'Sink' => 'Lavamanos', 'Garbage Disposal' => 'Triturador', 'Tank Water Heater' => 'Calentador de tanque', 'Tankless Water Heater' => 'Calentador sin tanque', 'Camera Inspection' => 'Inspección con cámara', 'Basic' => 'Básico', 'Standard Plus' => 'Estándar plus', 'Premium' => 'Premium', '1 Room' => '1 cuarto', '2 Rooms' => '2 cuartos', '3 Rooms' => '3 cuartos', 'Whole Home' => 'Casa completa'
            ],
            'products' => [
                'Interior Door Installation' => 'Instalación de puerta interior', 'Window Installation' => 'Instalación de ventana', 'Window Cleaning' => 'Limpieza de ventanas', 'Fence Installation' => 'Instalación de cerca', 'Engineering Digital Signature' => 'Firma digital de ingeniería',
                'Luxury Vinyl Plank Installation' => 'Instalación de vinyl plank de lujo', 'Laminate Flooring Installation' => 'Instalación de piso laminado', 'Tile Flooring Installation' => 'Instalación de losa', 'Engineered Hardwood Installation' => 'Instalación de madera engineered', 'Floor Removal' => 'Remoción de piso', 'Floor Leveling' => 'Nivelación de piso', 'Baseboard Installation' => 'Instalación de baseboard', 'Stair Tread Installation' => 'Instalación de escalones', 'Moisture Barrier Installation' => 'Instalación de barrera de humedad', 'Glue Down Vinyl Installation' => 'Instalación de vinyl pegado',
                'Exterior Door Installation' => 'Instalación de puerta exterior', 'French Door Installation' => 'Instalación de French door', 'Sliding Door Installation' => 'Instalación de puerta corrediza', 'Storm Door Installation' => 'Instalación de storm door', 'Door Trim Installation' => 'Instalación de moldura de puerta', 'Door Painting' => 'Pintura de puerta', 'Pocket Door Installation' => 'Instalación de pocket door', 'Barn Door Installation' => 'Instalación de barn door', 'Fire Rated Door Installation' => 'Instalación de puerta contra fuego', 'Door Hardware Installation' => 'Instalación de herrajes de puerta',
                'Window Replacement' => 'Reemplazo de ventana', 'Impact Window Upgrade' => 'Actualización a ventana de impacto', 'Window Trim Repair' => 'Reparación de moldura de ventana', 'Window Screen Replacement' => 'Reemplazo de screen de ventana', 'Window Caulking' => 'Calafateo de ventana', 'Window Disposal Service' => 'Servicio de botar ventanas', 'Second Floor Window Installation' => 'Instalación de ventana en segundo piso', 'Window Permit Assistance' => 'Ayuda con permiso de ventanas',
                'Bathroom Remodel Starter' => 'Remodelación básica de baño', 'Shower Tile Installation' => 'Instalación de tile en ducha', 'Vanity Installation' => 'Instalación de vanity', 'Toilet Installation' => 'Instalación de inodoro', 'Shower Glass Installation' => 'Instalación de vidrio de ducha', 'Bathroom Floor Tile' => 'Tile de piso de baño', 'Bathroom Exhaust Fan' => 'Extractor de baño', 'Tub to Shower Conversion' => 'Conversión de tina a ducha', 'Bathroom Demolition' => 'Demolición de baño', 'Waterproofing System' => 'Sistema de impermeabilización',
                'Kitchen Cabinet Installation' => 'Instalación de gabinetes de cocina', 'Countertop Installation' => 'Instalación de countertop', 'Backsplash Installation' => 'Instalación de backsplash', 'Kitchen Sink Installation' => 'Instalación de fregadero de cocina', 'Garbage Disposal Installation' => 'Instalación de triturador', 'Kitchen Faucet Installation' => 'Instalación de grifo de cocina', 'Cabinet Hardware Installation' => 'Instalación de herrajes de gabinete', 'Pantry Shelving' => 'Shelving de pantry', 'Kitchen Lighting' => 'Luces de cocina', 'Cabinet Removal' => 'Remoción de gabinetes',
                'Interior Painting' => 'Pintura interior', 'Exterior Painting' => 'Pintura exterior', 'Trim Painting' => 'Pintura de molduras', 'Ceiling Painting' => 'Pintura de cielos rasos', 'Door Painting Package' => 'Paquete de pintura de puertas', 'Drywall Texture Repair' => 'Reparación de textura drywall', 'Primer Application' => 'Aplicación de primer', 'Accent Wall Painting' => 'Pintura de pared acento', 'Cabinet Painting' => 'Pintura de gabinetes', 'Pressure Washing Before Paint' => 'Lavado a presión antes de pintar',
                'Drywall Patch Repair' => 'Reparación de parche de drywall', 'Drywall Hanging' => 'Instalación de drywall', 'Drywall Finishing' => 'Acabado de drywall', 'Texture Matching' => 'Igualar textura', 'Ceiling Drywall Repair' => 'Reparación de drywall en techo', 'Garage Drywall' => 'Drywall de garaje', 'Water Damage Drywall Repair' => 'Reparación de drywall por agua', 'Corner Bead Repair' => 'Reparación de corner bead', 'Drywall Sanding' => 'Lijado de drywall', 'Drywall Prime Ready Finish' => 'Acabado listo para primer',
                'Panel Upgrade' => 'Cambio de panel', 'EV Charger Installation' => 'Instalación de cargador EV', 'Ceiling Fan Installation' => 'Instalación de abanico de techo', 'Light Fixture Installation' => 'Instalación de lámpara', 'Outlet Installation' => 'Instalación de outlet', 'Switch Replacement' => 'Reemplazo de switch', 'Dedicated Circuit' => 'Circuito dedicado', 'Generator Inlet' => 'Entrada para generador', 'Smoke Detector Installation' => 'Instalación de detector de humo', 'Electrical Troubleshooting' => 'Diagnóstico eléctrico',
                'Water Heater Installation' => 'Instalación de calentador de agua', 'Tankless Water Heater' => 'Calentador sin tanque', 'Toilet Replacement' => 'Reemplazo de inodoro', 'Sink Installation' => 'Instalación de lavamanos', 'Leak Repair' => 'Reparación de fuga', 'Garbage Disposal Replacement' => 'Reemplazo de triturador', 'Shower Valve Replacement' => 'Reemplazo de válvula de ducha', 'Faucet Installation' => 'Instalación de grifo', 'Drain Cleaning' => 'Limpieza de drenaje', 'Plumbing Camera Inspection' => 'Inspección de plomería con cámara',
                'Mini Split Installation' => 'Instalación de mini split', 'Thermostat Installation' => 'Instalación de termostato', 'Duct Repair' => 'Reparación de ducto', 'Air Handler Platform' => 'Plataforma para air handler', 'Return Air Upgrade' => 'Mejora de retorno de aire', 'Supply Vent Installation' => 'Instalación de supply vent', 'Dryer Vent Cleaning' => 'Limpieza de dryer vent', 'HVAC Maintenance Visit' => 'Mantenimiento de aire acondicionado', 'Condensate Line Flush' => 'Limpieza de línea de condensado', 'Attic Duct Insulation' => 'Insulación de ducto en ático',
                'Shingle Roof Repair' => 'Reparación de techo shingle', 'Flat Roof Repair' => 'Reparación de techo plano', 'Roof Inspection' => 'Inspección de techo', 'Roof Tear Off' => 'Remoción de techo', 'Fascia Repair' => 'Reparación de fascia', 'Soffit Installation' => 'Instalación de soffit', 'Roof Vent Installation' => 'Instalación de ventilación de techo', 'Metal Roof Repair' => 'Reparación de techo metálico', 'Roof Permit Assistance' => 'Ayuda con permiso de techo', 'Gutter Installation' => 'Instalación de gutters',
                'Concrete Slab' => 'Losa de concreto', 'Driveway Concrete' => 'Concreto para driveway', 'Sidewalk Concrete' => 'Concreto para acera', 'Concrete Demo' => 'Demolición de concreto', 'Concrete Patch' => 'Parche de concreto', 'Footer Pour' => 'Vaciado de footer', 'Concrete Steps' => 'Escalones de concreto', 'Paver Removal' => 'Remoción de pavers', 'Concrete Sealing' => 'Sellado de concreto', 'Curb Repair' => 'Reparación de curb',
                'Wood Fence Installation' => 'Instalación de cerca de madera', 'Vinyl Fence Installation' => 'Instalación de cerca de vinilo', 'Chain Link Fence Installation' => 'Instalación de cerca cyclone', 'Fence Repair' => 'Reparación de cerca', 'Gate Installation' => 'Instalación de portón', 'Double Gate Installation' => 'Instalación de portón doble', 'Fence Removal' => 'Remoción de cerca', 'Fence Permit Help' => 'Ayuda con permiso de cerca', 'HOA Fence Package' => 'Paquete HOA para cerca', 'Post Replacement' => 'Reemplazo de poste',
                'Structural Engineering Letter' => 'Carta de ingeniería estructural', 'MEP Engineering Package' => 'Paquete de ingeniería MEP', 'Energy Calculations' => 'Cálculos energéticos', 'Wind Load Report' => 'Reporte de carga de viento', 'Permit Drawing Package' => 'Paquete de planos para permiso', 'As Built Drawing' => 'Plano as built', 'Site Plan Drafting' => 'Dibujo de site plan', 'Revision Response' => 'Respuesta de revisión', 'Digital Engineer Seal' => 'Sello digital de ingeniero', 'Commercial Permit Package' => 'Paquete de permiso comercial',
                'Interior Design Consultation' => 'Consulta de diseño interior', '3D Rendering' => 'Render 3D', 'Material Selection Board' => 'Tablero de selección de materiales', 'Kitchen Layout Design' => 'Diseño de layout de cocina', 'Bathroom Layout Design' => 'Diseño de layout de baño', 'Color Consultation' => 'Consulta de colores', 'Flooring Design Plan' => 'Plan de diseño de pisos', 'Lighting Design Plan' => 'Plan de iluminación', 'Cabinet Design Plan' => 'Plan de diseño de gabinetes', 'Project Scope Package' => 'Paquete de alcance de proyecto',
                'Post Construction Cleaning' => 'Limpieza post construcción', 'Move In Cleaning' => 'Limpieza para mudanza', 'Window Cleaning Package' => 'Paquete de limpieza de ventanas', 'Pressure Washing' => 'Lavado a presión', 'Debris Hauling' => 'Recogida de escombros', 'Garage Cleanout' => 'Limpieza de garaje', 'Appliance Cleaning' => 'Limpieza de electrodomésticos', 'Deep Cleaning' => 'Limpieza profunda', 'Final Touch Cleaning' => 'Limpieza final', 'Dust Control Cleaning' => 'Limpieza de control de polvo'
            ]
        ];
    }

    private function sc_translate($type, $value)
    {
        if ($this->sc_catalog_language() !== 'spanish') {
            return $this->sc_clean_catalog_text($value);
        }
        $translations = $this->sc_catalog_translations();
        $clean = $this->sc_clean_catalog_text($value);
        return $translations[$type][$clean] ?? $clean;
    }

    private function sc_clean_catalog_text($value)
    {
        $value = html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = strip_tags($value);
        $value = str_replace(["\xc2\xa0", '&nbsp;'], ' ', $value);
        return trim(preg_replace('/\s+/', ' ', $value));
    }


    private function sc_translate_description($product_name, $description)
    {
        $clean = $this->sc_clean_catalog_text($description);
        if ($this->sc_catalog_language() !== 'spanish') {
            return $clean;
        }
        $product = $this->sc_translate('products', $product_name);
        if (stripos($clean, 'Homeowner service package for') !== false || empty($clean)) {
            return 'Paquete de servicio residencial para ' . strtolower($product) . ' con opciones claras, precio inicial y flujo de trabajo de Smart Choice Contractors USA.';
        }
        return $clean;
    }

    private function sc_apply_product_language(&$product)
    {
        if (is_array($product)) {
            $product['display_product_name'] = $this->sc_translate('products', $product['product_name'] ?? '');
            $product['display_product_description'] = $this->sc_translate_description($product['product_name'] ?? '', $product['product_description'] ?? '');
            $product['display_category_name'] = $this->sc_translate('categories', $product['p_category_name'] ?? '');
            if (!empty($product['variations'])) {
                foreach ($product['variations'] as $variation) {
                    $variation->display_variation_name = $this->sc_translate('variations', $variation->variation_name ?? '');
                    $variation->display_variation_value = $this->sc_translate('values', $variation->variation_value ?? '');
                }
            }
        } elseif (is_object($product)) {
            $product->display_product_name = $this->sc_translate('products', $product->product_name ?? '');
            $product->display_product_description = $this->sc_translate_description($product->product_name ?? '', $product->product_description ?? '');
            $product->display_category_name = $this->sc_translate('categories', $product->p_category_name ?? '');
        }
    }

    private function sc_client_i18n()
    {
        return [
            'add_to_cart' => _l('add_to_cart'),
            'update_cart' => _l('update_cart'),
            'product_share' => _l('product_share'),
            'product_share_success' => _l('product_share_success'),
            'product_share_error' => _l('product_share_error'),
            'product_option' => _l('product_option'),
            'product_selection' => _l('product_selection'),
            'product_starting_at' => _l('product_starting_at'),
            'product_choose_option' => _l('product_choose_option'),
            'product_quantity_error' => _l('product_quantity_error'),
            'product_stock_error' => _l('product_stock_error'),
            'product_added_to_cart_success' => _l('product_added_to_cart_success'),
            'product_cart_updated_success' => _l('product_cart_updated_success'),
            'product_loading_catalog' => _l('product_loading_catalog'),
            'product_unable_load' => _l('product_unable_load'),
            'product_no_selection' => _l('product_no_selection'),
        ];
    }

    public function index()
    {
        if (0 != get_option('product_menu_disabled')) {
            set_alert('warning', _l('access_denied'));
            redirect(site_url());
        }
        $this->load->model('product_category_model');
        $data['title']              = _l('products');
        // The catalog is loaded by filter(); the HTML view only needs categories.
        $populatedCategories = array_fill_keys($this->products_model->get_populated_category_ids(), true);
        $data['product_categories'] = $this->product_category_model->get();
        foreach ($data['product_categories'] as $key => $category) {
            if (!isset($populatedCategories[$category['p_category_id']])) {
                unset($data['product_categories'][$key]);
                continue;
            }
            $data['product_categories'][$key]['display_name'] = $this->sc_translate('categories', $category['p_category_name'] ?? '');
        }
        $data['product_categories'] = array_values($data['product_categories']);
        usort($data['product_categories'], function ($a, $b) {
            return strcasecmp($a['display_name'], $b['display_name']);
        });
        $data['client_i18n'] = $this->sc_client_i18n();
        $this->data($data);
        $this->view('clients/products');
        $this->layout();
    }


    private function sc_product_gallery_urls($product, $gallery = null)
    {
        if ($gallery === null) {
            $gallery = products_get_gallery_images((int) $product['id']);
        }
        return products_catalog_artwork_urls($product, $gallery);
    }

    public function filter()
    {
        $p_category_id = $this->input->post('p_category_id');
        $cart_data     = ($this->session->userdata('cart_data') ?? []);
        $products      = $this->products_model->get_category_filter($p_category_id);
        $base_currency = $this->currencies_model->get_base_currency();
        $galleries = $this->products_model->get_catalog_gallery_images(array_column($products, 'id'));
        foreach ($products as $key => $value) {
            $products[$key]['cart_data']          = $this->get_cart_product($value['id']);
            $products[$key]['product_gallery_urls'] = $this->sc_product_gallery_urls($value, $galleries[(int) $value['id']] ?? []);
            $products[$key]['product_image_url'] = $products[$key]['product_gallery_urls'][0] ?? '';
            $products[$key]['no_image_url']       = module_dir_url('products', 'uploads') . '/image-not-available.png';
            $products[$key]['base_currency_name'] = $base_currency->name;
            $taxes                                = unserialize($value['taxes']);
            $total_tax                            = 0;
            if (!empty($taxes)) {
                foreach ($taxes as $tax) {
                    if (!is_array($tax)) {
                        $tmp_taxname = $tax;
                        $tax_array   = explode('|', $tax);
                    } else {
                        $tax_array   = explode('|', $tax['taxname']);
                        $tmp_taxname = $tax['taxname'];
                        if ('' == $tmp_taxname) {
                            continue;
                        }
                    }
                    $total_tax += $tax_array[1];
                }
            }
            $products[$key]['total_tax'] = $total_tax;
            $products[$key]['qty'] = _l('qty');
            $products[$key]['add_to_cart'] = _l('add_to_cart');
            $products[$key]['update_cart'] = _l('update_cart');
            $products[$key]['out_of_stock'] = _l('out_of_stock');
            $this->sc_apply_product_language($products[$key]);
        }
        echo json_encode($products);
    }

    private function sort_cart($cart_data)
    {
        if (!is_array($cart_data)) { return []; }
        foreach ($cart_data as $item) {
            if (!is_array($item) || !isset($item['product_id']) || !is_scalar($item['product_id'])
                || !ctype_digit((string)$item['product_id']) || (int)$item['product_id'] < 1) { return []; }
        }
        $cart_data = array_values($cart_data);
        $cart_data_keys = array_keys($cart_data);
        $first_index = 0;
        while ($first_index < count($cart_data_keys) - 1) {
            $sorted_count = 0;
            for ($second_index = $first_index + 2; $second_index < count($cart_data_keys); $second_index++) {
                if ($cart_data[$cart_data_keys[$first_index]]['product_id'] == $cart_data[$cart_data_keys[$second_index]]['product_id']) {
                    $replace_cart_item = $cart_data[$cart_data_keys[$second_index]];
                    for ($third_index = $second_index; $third_index > $first_index + $sorted_count + 1; $third_index--) {
                        $cart_data[$cart_data_keys[$third_index]] = $cart_data[$cart_data_keys[$third_index - 1]];
                    }
                    $cart_data[$cart_data_keys[$first_index + $sorted_count + 1]] = $replace_cart_item;
                    $sorted_count = $sorted_count + 1;
                }
            }
            $first_index = $first_index + $sorted_count + 1;
        }
        return $cart_data;
    }

    public function add_cart()
    {
        $product_id           = $this->input->post('product_id');
        $product_variation_id = $this->input->post('product_variation_id');
        $quantity             = max(1, (int) $this->input->post('quantity'));
        $newdata['cart_data'] = ($this->session->userdata('cart_data') ?? []);
        if (empty($newdata['cart_data'])) {
            $newdata['cart_data'] = [
                ['product_id' => $product_id, 'product_variation_id' => $product_variation_id, 'quantity' => $quantity]
            ];
            $this->session->set_userdata($newdata);
        } else {
            $cart_item_exist = false;
            foreach ($newdata['cart_data'] as $cart_item_index => $cart_item) {
                if ($cart_item['product_id'] == $product_id && ($cart_item['product_variation_id'] ?? '') == $product_variation_id) {
                    $newdata['cart_data'][$cart_item_index]['quantity'] = $quantity;
                    $cart_item_exist = true;
                }
            }
            if (!$cart_item_exist) {
                $newdata['cart_data'][] = ['product_id' => $product_id, 'product_variation_id' => $product_variation_id, 'quantity' => $quantity];
            }
            $newdata['cart_data'] = $this->sort_cart($newdata['cart_data']);
            $this->session->set_userdata($newdata);
        }
        
        echo json_encode(($this->session->userdata('cart_data') ?? []));
    }

    public function remove_cart($product_id = null, $product_variation_id = null, $return = false)
    {
        if (empty($product_id)) {
            $product_id = $this->input->post('product_id');
        }
        if ($product_variation_id === null) {
            $product_variation_id = $this->input->post('product_variation_id');
        }
        $newdata['cart_data'] = ($this->session->userdata('cart_data') ?? []);
        foreach ($newdata['cart_data'] as $key => $value) {
            if ($product_id == $value['product_id'] && ($product_variation_id ?? '') == ($value['product_variation_id'] ?? '')) {
                unset($newdata['cart_data'][$key]);
            }
        }
        $cart_data = [];
        foreach ($newdata['cart_data'] as $value) {
            $cart_data[] = $value;
        }
        $newdata['cart_data'] = $cart_data;
        $this->session->set_userdata($newdata);
        if (empty($newdata['cart_data'])) {
            set_alert('danger', _l('Cart is empty'));
            $res['status'] = false;
            if ($return) {
                return json_encode($res);
            }
            echo json_encode($res);

            return;
        }
        $res['status'] = true;
        $res['cart_data'] = $newdata['cart_data'];
        if ($return) {
            return json_encode($res);
        }
        echo json_encode($res);
    }

    public function get_currency($id)
    {
        echo json_encode(get_currency($id));
    }

    public function place_order($product_id = false)
    {
        if (0 != get_option('product_menu_disabled')) {
            $this->session->unset_userdata('cart_data');
            set_alert('warning', _l('access_denied'));
            redirect(site_url());
        }
        $this->load->model('products/order_model');
        if (!is_client_logged_in()) {
            set_alert('warning', _l('clients_login_heading_no_register'));
            redirect(site_url(''));
        }
        $message          = '';
        $post = $this->input->post();
        unset($post['taxes']);
        unset($post['shipping_cost']);
        if (!empty($post)) {
            // Customer checkout always bills the signed-in customer; staff POS remains separate.
            $post['clientid'] = get_client_user_id();
            $post['product_items'] = $this->sort_cart($post['product_items'] ?? []);
            $return_data = $this->order_model->add_invoice_order($post);
            if ($return_data['status']) {
                $this->session->unset_userdata('cart_data');
                set_alert('success', _l('order_success'));
                if ($return_data['single_invoice']) {
                    redirect(site_url('invoice/' . $return_data['invoice_id'] . '/' . $return_data['invoice_hash']), 'refresh');
                }
                redirect(site_url('clients/invoices'), 'refresh');
            }
            if (!$return_data['status']) {
                set_alert('error', _l('order_fail'));
                $message .= $return_data['message'];
            }
        }
        if (empty(($this->session->userdata('cart_data') ?? []))) {
            set_alert('danger', _l('Cart is empty'));
            redirect(site_url('products/client/'));
        }
        $cart_data = $this->sort_cart(($this->session->userdata('cart_data') ?? []));
        if (empty($cart_data)) {
            set_alert('danger', _l('Cart is empty'));
            redirect(site_url('products/client/'));
        }
        $data['products'] = $product = $this->products_model->get_by_cart_product($cart_data);
        if (empty($product)) {
            set_alert('danger', _l('Products in Cart not found'));
            redirect(site_url('products/client/'));
        }
        $all_taxes        = [];
        $init_tax         = [];
        $apply_shipping   = false;
        foreach ($product as $value) {
            if (!$value->is_digital) {
                if ((int) $value->quantity_number < 1) {
                    $this->remove_cart($value->id, $value->product_variation_id ?? '', true);
                    $message .= $value->product_name . ' is out of stock so removed from cart <br>';
                    continue;
                }
                if ((int) $value->quantity > (int) $value->quantity_number) {
                    $value->quantity = $value->quantity_number;
                    $message         .= $value->product_name . ' is only ' . $value->quantity_number . ' in stock so quantity reduced to that quantity <br>';
                }
            }
            $value->apply_shipping = false;
            if (!$value->recurring && !$value->is_digital) {
                $value->apply_shipping = true;
                $apply_shipping = true;
            }
            $taxes_arr       = [];
            $value->taxname  = $taxes  = unserialize($value->taxes);
            if ($taxes) {
                foreach ($taxes as $tax) {
                    if (!is_array($tax)) {
                        $tmp_taxname = $tax;
                        $tax_array   = explode('|', $tax);
                    } else {
                        $tax_array   = explode('|', $tax['taxname']);
                        $tmp_taxname = $tax['taxname'];
                        if ('' == $tmp_taxname) {
                            continue;
                        }
                    }
                    $init_tax[$tmp_taxname][]  = ($value->rate * $value->quantity) / 100 * $tax_array[1];
                    $all_taxes[$tmp_taxname]   = $taxes_arr[]   = ['name' => $tmp_taxname, 'taxrate' => $tax_array[1], 'taxname' => $tax_array[0]];
                }
            }
            $value->taxes = $taxes_arr;
        }
        $shipping_cost = 0;
        $base_shipping_cost = 0;
        $shipping_tax = 0;
        if ($apply_shipping) {
            $taxname = (!empty((get_option('product_tax_for_shipping_cost')))) ? unserialize(get_option('product_tax_for_shipping_cost')) : '';
            $shipping_cost = $base_shipping_cost = get_option('product_flat_rate_shipping');
            $shipping_tax = 0;
            if ($taxname) {
                foreach ($taxname as $tax) {
                    if (!is_array($tax)) {
                        $tmp_taxname = $tax;
                        $tax_array   = explode('|', $tax);
                    } else {
                        $tax_array   = explode('|', $tax['taxname']);
                        $tmp_taxname = $tax['taxname'];
                        if ('' == $tmp_taxname) {
                            continue;
                        }
                    }
                    $shipping_tax  += $tax_array[1];
                    $shipping_cost += ($base_shipping_cost) / 100 * $tax_array[1];
                }
            }
        }
        $data['shipping_cost']    = $shipping_cost;
        $data['shipping_base']    = $base_shipping_cost;
        $data['shipping_tax']     = $shipping_tax;
        $data['all_taxes']        = $all_taxes;
        $data['init_tax']         = $init_tax;
        $data['message']          = $message;
        $data['title']            = _l('confirm') . ' ' . _l('place_order');
        $data['base_currency']    = $this->currencies_model->get_base_currency();
        $this->data($data);
        $this->view('clients/place_order');
        $this->layout();
    }

    public function variation_values()
    {
        $product_id = $this->input->post('product_id');
        $variation_id = $this->input->post('variation_id');
        $variations = $this->products_model->get_by_id_variation_values($product_id, $variation_id);
        foreach ($variations as $variation) {
            $variation->display_variation_name = $this->sc_translate('variations', $variation->variation_name ?? '');
            $variation->display_variation_value = $this->sc_translate('values', $variation->variation_value ?? '');
        }
        echo json_encode($variations);
    }

    private function get_tax_shipping()
    {
        $cart_data = ($this->session->userdata('cart_data') ?? []);
        if (empty($cart_data)) {
            set_alert('danger', _l('Cart is empty'));
            redirect(site_url('products/client/'));
        }
        $product = $this->products_model->get_by_cart_product($cart_data);
        if (empty($product)) {
            set_alert('danger', _l('Products in Cart not found'));
            redirect(site_url('products/client/'));
        }

        $all_taxes        = [];
        $init_tax         = [];
        $apply_shipping   = false;
        foreach ($product as $value) {
            $value->apply_shipping = false;
            if (!$value->recurring && !$value->is_digital) {
                $value->apply_shipping = true;
                $apply_shipping = true;
            }
            $taxes_arr       = [];
            $value->taxname  = $taxes  = unserialize($value->taxes);
            if ($taxes) {
                foreach ($taxes as $tax) {
                    if (!is_array($tax)) {
                        $tmp_taxname = $tax;
                        $tax_array   = explode('|', $tax);
                    } else {
                        $tax_array   = explode('|', $tax['taxname']);
                        $tmp_taxname = $tax['taxname'];
                        if ('' == $tmp_taxname) {
                            continue;
                        }
                    }
                    $init_tax[$tmp_taxname][]  = ($value->rate * $value->quantity) / 100 * $tax_array[1];
                    $all_taxes[$tmp_taxname]   = $taxes_arr[]   = ['name' => $tmp_taxname, 'taxrate' => $tax_array[1], 'taxname' => $tax_array[0]];
                }
            }
            $value->taxes = $taxes_arr;
        }
        $shipping_cost = 0;
        $base_shipping_cost = 0;
        $shipping_tax = 0;
        if ($apply_shipping) {
            $taxname = (!empty((get_option('product_tax_for_shipping_cost')))) ? unserialize(get_option('product_tax_for_shipping_cost')) : '';
            $shipping_cost = $base_shipping_cost = get_option('product_flat_rate_shipping');
            $shipping_tax = 0;
            if ($taxname) {
                foreach ($taxname as $tax) {
                    if (!is_array($tax)) {
                        $tmp_taxname = $tax;
                        $tax_array   = explode('|', $tax);
                    } else {
                        $tax_array   = explode('|', $tax['taxname']);
                        $tmp_taxname = $tax['taxname'];
                        if ('' == $tmp_taxname) {
                            continue;
                        }
                    }
                    $shipping_tax  += $tax_array[1];
                    $shipping_cost += ($base_shipping_cost) / 100 * $tax_array[1];
                }
            }
        }

        return [
            'product' => $product,
            'all_taxes' => $all_taxes,
            'init_tax' => $init_tax,
            'apply_shipping' => $apply_shipping,
            'shipping_cost' => $shipping_cost,
            'base_shipping_cost' => $base_shipping_cost,
            'shipping_tax' => $shipping_tax,
        ];
    }


    public function share_product()
    {
        $product_id = (int) $this->input->post('product_id');
        $email = trim((string) $this->input->post('email'));
        if ($product_id < 1 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => _l('product_share_error')]);
            return;
        }
        $product = $this->products_model->get_by_id_product($product_id);
        if (!$product) {
            echo json_encode(['success' => false, 'message' => _l('product_share_error')]);
            return;
        }
        $this->sc_apply_product_language($product);
        $product_name = $product->display_product_name ?: $product->product_name;
        $product_url = site_url('products/client?product=' . $product_id);
        $subject = 'Check out this product';
        if ($this->sc_catalog_language() === 'spanish') {
            $subject = 'Chequea este producto';
        }
        $message = '<p>I thought you might be interested in this service.</p>';
        if ($this->sc_catalog_language() === 'spanish') {
            $message = '<p>Creo que este servicio te puede interesar.</p>';
        }
        $message .= '<p><strong>' . htmlspecialchars($product_name) . '</strong></p>';
        $message .= '<p><a href="' . htmlspecialchars($product_url) . '">' . htmlspecialchars($product_url) . '</a></p>';
        $message .= '<p>' . htmlspecialchars(get_option('companyname')) . '</p>';
        $this->load->library('email');
        $from = get_option('smtp_email');
        if (empty($from)) {
            $from = get_option('email_header');
        }
        $this->email->clear(true);
        if (!empty($from)) {
            $this->email->from($from, get_option('companyname'));
        }
        $this->email->to($email);
        $this->email->subject($subject);
        $this->email->message($message);
        $sent = $this->email->send();
        echo json_encode(['success' => (bool) $sent, 'message' => $sent ? _l('product_share_success') : _l('product_share_error')]);
    }

    public function apply_coupon($coupon_code = null)
    {
        if (0 != get_option('coupons_disabled')) {
            set_alert('warning', _l('access_denied'));
            redirect(site_url());
        }

        if (empty($coupon_code)) {
            $coupon_code = $this->input->post('coupon_code');
        }
        
        $this->load->model('products/products_model');
        
        $base_currency = $this->currencies_model->get_base_currency();

        $this->load->model('products/coupons_model');
        $coupon = $this->coupons_model->get_by_code($coupon_code);

        if ($coupon) {
            if ($this->coupons_model->is_available($coupon->id)) {
                $total = 0;
                $tax_shipping_data = $this->get_tax_shipping();
                foreach ($tax_shipping_data['product'] as $value) {
                    $total += $value->quantity * $value->rate;
                }
                foreach ($tax_shipping_data['all_taxes'] as $tax) {
                    $total += array_sum($tax_shipping_data['init_tax'][$tax['name']]);
                }
                if (!empty($tax_shipping_data['shipping_cost'])) {
                    $total += $tax_shipping_data['shipping_cost'];
                }
                if ($coupon->type == '%') {
                    $coupon_discount = $total * $coupon->amount / 100;
                } else {
                    $coupon_discount = $coupon->amount;
                }
                $total -= $coupon_discount;
                $res = [
                    'status' => true,
                    'coupon_id' => $coupon->id,
                    'coupon_discount' => app_format_money($coupon_discount, $base_currency->name),
                    'total' => app_format_money($total, $base_currency->name)
                ];
            } else {
                $res = [
                    'status' => false,
                    'message' => _l('coupon_can_not_apply')
                ];
            }
        } else {
            $res = [
                'status' => false,
                'message' => _l('coupon_does_not_exist')
            ];
        }
        echo json_encode($res);
    }

    public function remove_coupon()
    {
        if (0 != get_option('coupons_disabled')) {
            set_alert('warning', _l('access_denied'));
            redirect(site_url());
        }
        
        $this->load->model('products/products_model');
        
        $base_currency = $this->currencies_model->get_base_currency();

        $total = 0;
        $tax_shipping_data = $this->get_tax_shipping();
        foreach ($tax_shipping_data['product'] as $value) {
            $total += $value->quantity * $value->rate;
        }
        foreach ($tax_shipping_data['all_taxes'] as $tax) {
            $total += array_sum($tax_shipping_data['init_tax'][$tax['name']]);
        }
        if (!empty($tax_shipping_data['shipping_cost'])) {
            $total += $tax_shipping_data['shipping_cost'];
        }
        $res = [
            'status' => true,
            'total' => app_format_money($total, $base_currency->name)
        ];
        echo json_encode($res);
    }
}