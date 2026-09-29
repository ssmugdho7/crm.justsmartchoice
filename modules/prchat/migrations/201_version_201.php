<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Migration 200 - Version 2.0 - Complete Chatbot System
 * 
 * Creates the full chatbot architecture:
 * - Chatbots (main configuration per chatbot instance)
 * - Conversations (visitor sessions with session tracking)
 * - Messages (conversation history with AI tracking)
 * - Leads (captured visitor information)
 * - Training Data (texts, links, files, Q&A for RAG)
 * - Notification Staff (staff members to notify on escalation)
 */
class Migration_Version_201 extends App_module_migration
{
    public function up()
    {
        // No changes needed - migration 200 contains all updates
    }
}
