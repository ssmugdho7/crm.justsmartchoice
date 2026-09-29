<?php

namespace modules\whatsbot\traits;

use LLPhant\Chat\OpenAIChat;
use LLPhant\Embeddings\DataReader\FileDataReader;
use LLPhant\Embeddings\DocumentSplitter\DocumentSplitter;
use LLPhant\Embeddings\EmbeddingFormatter\EmbeddingFormatter;
use LLPhant\Embeddings\EmbeddingGenerator\OpenAI\OpenAI3SmallEmbeddingGenerator;
use LLPhant\Embeddings\VectorStores\FileSystem\FileSystemVectorStore;
use LLPhant\OpenAIConfig;
use LLPhant\Query\SemanticSearch\QuestionAnswering;
use OpenAI;
use Exception;

trait OpenAiAssistantTraits
{
    protected ?OpenAIConfig $openAIConfig = null;
    protected ?string $docPath = null;

    /**
     * Initialize OpenAI Configuration and Paths
     *
     * @param string $apiKey OpenAI API Key
     * @param string $docPath Base path for documents
     * @return void
     */
    public function initializeOpenAI(string $docPath): void
    {
        $this->configureOpenAI($this->getOpenAiKey());
        $this->setDocumentPath($docPath);
    }

    /**
     * Retrieves the OpenAI API key from the options.
     *
     * @return string|null The OpenAI API key.
     */
    public function getOpenAiKey()
    {
        return get_option('wb_open_ai_key');
    }

    /**
     * Create File Chunk and Save Embeddings
     *
     * @param string $fileName
     * @param int $chunkSize
     * @return bool
     */
    public function createFileChunk(string $fileName, int $chunkSize = 800): bool
    {
        try {
            $splitDocuments = $this->readAndSplitDocument($fileName, $chunkSize);
            $formattedDocuments = $this->formatDocumentChunks($splitDocuments);
            $embeddedDocuments = $this->generateEmbeddings($formattedDocuments);
            return $this->saveEmbeddingsToVectorStore($embeddedDocuments, $fileName);
        } catch (\Throwable $e) {
            log_message('error', 'Error in createFileChunk: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get AI Answer from Vector Store and Question
     *
     * @param string $fileName
     * @param string $question
     * @return string|null
     */
    public function getAIAnswer(string $fileName, string $question): ?string
    {
        try {
            $fileSystemVectorStore = $this->loadVectorStore($fileName);
            $fileSystemVectorStore->fetchDocumentsByChunkRange("files", str_replace(FCPATH, '', $this->docPath) . $fileName, 0, 999999);
            if (!$fileSystemVectorStore) {
                return null;
            }
            $embeddingGenerator = new OpenAI3SmallEmbeddingGenerator($this->openAIConfig);
            $qa = new QuestionAnswering($fileSystemVectorStore, $embeddingGenerator, new OpenAIChat($this->openAIConfig));
            $customSystemMessage = 'Answer only based on the provided context. \\n\\n{context}.';
            $qa->systemMessageTemplate = $customSystemMessage;
            return $qa->answerQuestion($question);
        } catch (\Throwable $e) {
            log_message('error', 'Error in getAIAnswer: ' . $e->getMessage());
            return null;
        }
    }

    public function listModel(): array
    {
        try {
            $openAiKey = $this->getOpenAiKey();
            $openAi = new OpenAI();
            $client = $openAi->client($openAiKey);
            $response = $client->models()->list();

            if ($response === null || !is_object($response)) {
                throw new \RuntimeException('Invalid response format from OpenAI API.');
            }

            // Check for errors in response
            if (property_exists($response, 'error')) {
                update_option('wb_open_ai_key_verify', 0, 0);
                update_option('wb_openai_model', '', 0);
                return [
                    'status' => false,
                    'message' => $response->error->message ?? 'Unknown error occurred.',
                ];
            }

            // Update successful key verification
            update_option('wb_open_ai_key_verify', 1, 0);
            return [
                'status' => true,
                'data' => 'Model list fetched successfully.',
            ];
        } catch (\Throwable $th) {
            log_message('error', 'Error in listModel: ' . $th->getMessage());
            return [
                'status' => false,
                'message' => _l('incorrect_api_key_provided'),
            ];
        }
    }

    /**
     * Sends a request to the OpenAI API to get a response based on provided data.
     *
     * @param array $data The data to be sent to the OpenAI API.
     *
     * @return array Contains status and message of the response.
     */
    public function aiResponse(array $data)
    {
        try {
            $config = new OpenAIConfig();
            $config->apiKey = $this->getOpenAiKey();
            $config->model = get_option('wb_openai_model');
            $chat = new OpenAIChat($config);
            $message = $data['input_msg'];
            $menuItem = $data['menu'];
            $submenuItem = $data['submenu'];
            $status = true;

            $prompt = match ($menuItem) {
                'Simplify Language' => 'You will be provided with statements, and your task is to convert them to Simplify Language. but don\'t change inputed language.',
                'Fix Spelling & Grammar' => 'You will be provided with statements, and your task is to convert them to standard Language. but don\'t change inputed language.',
                'Translate' => 'You will be provided with a sentence, and your task is to translate it into ' . $submenuItem . ', only give translated sentance',
                'Change Tone' => 'You will be provided with statements, and your task is to change tone into ' . $submenuItem . '. but don\'t chnage inputed language.',
                'Custom Prompt' => $submenuItem,
            };

            $messages = [
                ['role' => 'system', 'content' => $prompt],
                ['role' => 'user', 'content' => $message],
            ];

            // Send the structured messages to OpenAI's chat API
            $response = $chat->generateChat($messages);
        } catch (\Throwable $th) {
            $status = false;
            $message = _l('something_went_wrong');
        }

        return [
            'status' => $status,
            'message' => $status ? $response : $message,
        ];
    }

    /**
     * Configure OpenAI API Key
     *
     * @param string $apiKey
     * @return void
     */
    protected function configureOpenAI(string $apiKey): void
    {
        $this->openAIConfig = new OpenAIConfig();
        $this->openAIConfig->apiKey = $apiKey;
        $this->openAIConfig->model = get_option('wb_pa_model');
        $this->openAIConfig->modelOptions = [
            'temperature' => (int) get_option('pa_temperature'),
            'top_p' => 0.5,
            'max_tokens' => (int) get_option('pa_max_token'),
        ];
    }

    /**
     * Set Document Path
     *
     * @param string $docPath
     * @return void
     */
    protected function setDocumentPath(string $docPath): void
    {
        $this->docPath = rtrim($docPath, '/') . '/';
    }

    /**
     * Read and Split Documents into Chunks
     *
     * @param string $fileName
     * @param int $chunkSize
     * @return array
     */
    protected function readAndSplitDocument(string $fileName, int $chunkSize): array
    {
        $filePath = $this->docPath . $fileName;
        $reader = new FileDataReader($filePath);
        $documents = $reader->getDocuments();
        if (!count(array_filter(array_column($documents, 'content')))) {
            throw new Exception("Error Processing Request");
        }
        return DocumentSplitter::splitDocuments($documents, $chunkSize);
    }

    /**
     * Format Document Chunks for Embedding
     *
     * @param array $splitDocuments
     * @return array
     */
    protected function formatDocumentChunks(array $splitDocuments): array
    {
        return EmbeddingFormatter::formatEmbeddings($splitDocuments);
    }

    /**
     * Generate Embeddings for Document Chunks
     *
     * @param array $formattedDocuments
     * @return array
     */
    protected function generateEmbeddings(array $formattedDocuments): array
    {
        $embeddingGenerator = new OpenAI3SmallEmbeddingGenerator($this->openAIConfig);
        return $embeddingGenerator->embedDocuments($formattedDocuments);
    }

    /**
     * Save Embeddings to Vector Store
     *
     * @param array $embeddedDocuments
     * @param string $fileName
     * @return bool
     */
    protected function saveEmbeddingsToVectorStore(array $embeddedDocuments, string $fileName): bool
    {
        $vectorStorePath = $this->docPath . 'chunks/' . pathinfo($fileName, PATHINFO_FILENAME) . '.json';
        $fileSystemVectorStore = new FileSystemVectorStore($vectorStorePath);
        $fileSystemVectorStore->addDocuments($embeddedDocuments);
        $chunkFile = json_decode(file_get_contents($vectorStorePath));
        if ($chunkFile && !count(array_filter(array_column($chunkFile, 'content')))) {
            throw new Exception("Error Processing Request");
        }
        return true;
    }

    /**
     * Load Vector Store for a Document
     *
     * @param string $fileName
     * @return FileSystemVectorStore|null
     */
    protected function loadVectorStore(string $fileName): ?FileSystemVectorStore
    {
        try {
            $vectorStorePath = $this->docPath . 'chunks/' . pathinfo($fileName, PATHINFO_FILENAME) . '.json';
            return new FileSystemVectorStore($vectorStorePath);
        } catch (\Throwable $e) {
            log_message('error', 'Error in loadVectorStore: ' . $e->getMessage());
            return null;
        }
    }
}
