<?php
/**
 * Input Validation Helper
 */

class Validator {
    private $errors = [];
    private $data = [];
    
    public function __construct($data) {
        $this->data = $data;
    }
    
    /**
     * Validate required field
     */
    public function required($field, $message = null) {
        if (!isset($this->data[$field]) || empty(trim($this->data[$field]))) {
            $this->errors[$field] = $message ?: "The {$field} field is required";
        }
        return $this;
    }
    
    /**
     * Validate email
     */
    public function email($field, $message = null) {
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = $message ?: "The {$field} must be a valid email address";
        }
        return $this;
    }
    
    /**
     * Validate minimum length
     */
    public function min($field, $length, $message = null) {
        if (isset($this->data[$field]) && strlen($this->data[$field]) < $length) {
            $this->errors[$field] = $message ?: "The {$field} must be at least {$length} characters";
        }
        return $this;
    }
    
    /**
     * Validate maximum length
     */
    public function max($field, $length, $message = null) {
        if (isset($this->data[$field]) && strlen($this->data[$field]) > $length) {
            $this->errors[$field] = $message ?: "The {$field} must not exceed {$length} characters";
        }
        return $this;
    }
    
    /**
     * Validate numeric
     */
    public function numeric($field, $message = null) {
        if (isset($this->data[$field]) && !is_numeric($this->data[$field])) {
            $this->errors[$field] = $message ?: "The {$field} must be a number";
        }
        return $this;
    }
    
    /**
     * Validate in array
     */
    public function in($field, $values, $message = null) {
        if (isset($this->data[$field]) && !in_array($this->data[$field], $values)) {
            $this->errors[$field] = $message ?: "The {$field} must be one of: " . implode(', ', $values);
        }
        return $this;
    }
    
    /**
     * Check if validation passed
     */
    public function passes() {
        return empty($this->errors);
    }
    
    /**
     * Check if validation failed
     */
    public function fails() {
        return !$this->passes();
    }
    
    /**
     * Get errors
     */
    public function errors() {
        return $this->errors;
    }
    
    /**
     * Get validated data
     */
    public function validated() {
        return $this->data;
    }
}
