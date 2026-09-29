<?php

namespace TourGuide;

/**
 * TourGuideRepository
 *
 * Handles database interactions for the tour guide feature.
 * Provides methods for retrieving, adding, updating, and deleting tour guide records.
 */
class TourGuideRepository
{
    /** @var TourGuideDB $db */
    private $db;

    private $table;
    private $metaTable;

    function __construct()
    {
        $this->db = tourGuideHelper()->dbInstance();

        // Initialize the table name with the prefix
        $this->table = 'tour_guide';
        $this->metaTable = 'tour_guide_metadata';
    }

    /**
     * Retrieve all tour guide records from the database.
     *
     * @return array An array of tour guide records.
     */
    public function getAll()
    {
        return $this->db->read($this->table, [], '*', 'priority DESC');
    }

    /**
     * Retrieve a specific tour guide record by its ID.
     *
     * @param int $id The ID of the tour guide to retrieve.
     * @return object|null The tour guide record as an object.
     */
    public function get($id, $format = 'object')
    {
        $results = $this->db->read($this->table, ['id' => $id]);
        if (empty($results)) return null;
        return  $format == 'object' ? (object)$results[0] : $results[0];
    }

    /**
     * Save a tour guide record to the database.
     *
     * If an ID is provided, the record is updated. Otherwise, a new record is added.
     *
     * @param array $data The tour guide data to save.
     * @return int The ID of the inserted or updated record.
     */
    public function save($data)
    {
        if (!empty($data['id'])) {
            return $this->update($data, $data['id']);
        } else {
            return $this->add($data);
        }
    }

    /**
     * Add a new tour guide record to the database.
     *
     * @param array $data The tour guide data to add.
     * @return int The ID of the newly inserted record.
     */
    public function add($data)
    {
        return $this->db->create($this->table, $data);
    }

    /**
     * Update an existing tour guide record in the database.
     *
     * @param array $data The tour guide data to update.
     * @param int $id The ID of the tour guide to update.
     * @return int The number of affected rows.
     */
    public function update($data, $id)
    {
        return $this->db->update($this->table, $data, ['id' => $id]);
    }

    /**
     * Clone an existing tour guide record in the database.
     *
     * @param int $id The ID of the tour guide to clone.
     * @return int The number of affected rows.
     */
    public function clone($id)
    {
        $tour_guide = $this->get($id);
        $data = (array)$tour_guide;
        $data['title'] .= ' #copy';

        unset($data['id']);
        unset($data['created_at']);

        return $this->save($data);
    }

    /**
     * Delete a tour guide record from the database.
     *
     * @param int $id The ID of the tour guide to delete.
     * @return int The number of affected rows.
     */
    public function delete($id)
    {
        return $this->db->delete($this->table, ['id' => $id]);
    }


    /**
     * Retrieves the settings with an optional cache mechanism.
     *
     * @param bool $allowCache - Determines whether caching should be used.
     * @return array - The settings data.
     */
    public function getSettings($allowCache = true)
    {
        // Use a static variable to store the cache between function calls
        static $cachedSettings = null;

        // Check if caching is allowed and if settings are already cached
        if ($allowCache && $cachedSettings !== null) {
            return $cachedSettings; // Return cached settings
        }

        // If not cached or cache is bypassed, retrieve settings from the source
        $settings = $this->getMetadata('settings');

        // Cache the settings for future use
        if ($allowCache) {
            $cachedSettings = $settings;
        }

        return $settings;
    }

    /**
     * Updates the settings metadata.
     * 
     * This function updates the 'settings' metadata by passing the data to the updateMetadata method.
     * It ensures the data is properly stored in the metadata table under the 'settings' name.
     * 
     * @param array $data - The new settings data to be updated.
     * @return array - The updated settings data.
     */
    public function updateSettings($data)
    {
        return $this->updateMetadata('settings', $data);
    }

    /**
     * Retrieves user-specific metadata.
     * 
     * This function fetches metadata associated with a specific user based on their user ID.
     * The metadata is stored under a key that includes the user ID (e.g., 'user_1').
     * 
     * @param int $userId - The ID of the user whose metadata is being retrieved.
     * @return array - The metadata for the specified user.
     */
    public function getUserMetadata($userId)
    {
        return $this->getMetadata('user_' . $userId);
    }

    /**
     * Updates user-specific metadata.
     * 
     * This function updates the metadata for a specific user by passing the user ID and data to updateMetadata.
     * The metadata is stored under a key that includes the user ID (e.g., 'user_1').
     * 
     * @param int $userId - The ID of the user whose metadata is being updated.
     * @param array $data - The new metadata to be updated for the user.
     * @return array - The updated metadata for the specified user.
     */
    public function updateUserMetadata($userId, $data)
    {
        return $this->updateMetadata('user_' . $userId, $data);
    }

    /**
     * Retrieves metadata from the database.
     * 
     * This function fetches metadata from the database using the provided name key.
     * The data is decoded from JSON format and returned. If the 'pretty' option is true, 
     * the decoded value is returned directly. Otherwise, the full database entry is returned.
     * 
     * @param string $name - The name of the metadata entry to retrieve.
     * @param bool $pretty - Whether to return only the value (true) or the full entry (false).
     * @return array - The retrieved metadata.
     */
    public function getMetadata($name, $pretty = true)
    {
        // Fetch the metadata entry from the database using the provided name
        $data = $this->db->read($this->metaTable, ['name' => $name])[0] ?? [];

        // Return an empty array if no data is found
        if (empty($data)) return [];

        // Decode the value from JSON format
        $data['value'] = json_decode($data['value'], true);

        // Return only the value if 'pretty' is true
        if ($pretty) return $data['value'];

        // Return the full metadata entry otherwise
        return $data;
    }

    /**
     * Updates metadata in the database.
     * 
     * This function updates or creates metadata for a given name.
     * If the metadata exists, the new data is merged with the old data and updated in the database.
     * If the metadata does not exist, it is created with the provided name and data.
     * 
     * @param string $name - The name of the metadata entry to update or create.
     * @param array $data - The new data to be merged or created in the metadata.
     * @return array - The updated or newly created metadata.
     */
    public function updateMetadata($name, $data)
    {
        // Fetch the existing metadata for the given name
        $oldData = $this->getMetadata($name, false);

        // If no existing metadata, create a new entry
        if (empty($oldData['name'])) {
            $this->db->create($this->metaTable, ['value' => json_encode($data), 'name' => $name]);
            return $data;
        }

        // If the metadata name doesn't match the old name, return the new data without updating
        if ($name !== $oldData['name'])
            return $data;

        // Merge the old metadata with the new data
        $newData = array_merge($oldData['value'], $data);

        // Update the existing metadata entry with the new merged data
        $this->db->update($this->metaTable, ['value' => json_encode($newData)], ['name' => $name, 'id' => $oldData['id']]);

        return $newData; // Return the merged data
    }
}
