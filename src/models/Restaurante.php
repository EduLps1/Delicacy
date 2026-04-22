<?php
// src/models/Restaurant.php

namespace Delicacy\Models;

class Restaurant extends BaseModel
{
    protected $table = 'restaurants';

    public function findByUserId($user_id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function createRestaurant($user_id, $name, $email, $phone, $cnpj)
    {
        $data = [
            'user_id' => $user_id,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'cnpj' => $cnpj,
            'commission_type' => COMMISSION_HYBRID,
            'active_commission_rate' => 2.5,
            'is_active' => RESTAURANT_ACTIVE,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return $this->insert($data);
    }

    public function getActiveRestaurants()
    {
        $sql = "SELECT * FROM {$this->table} WHERE is_active = " . RESTAURANT_ACTIVE;
        $result = $this->db->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
}

?>