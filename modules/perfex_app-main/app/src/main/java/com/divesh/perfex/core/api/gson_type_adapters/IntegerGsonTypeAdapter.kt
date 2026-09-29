package com.divesh.perfex.core.api.gson_type_adapters

import com.google.gson.*
import com.google.gson.stream.JsonReader
import com.google.gson.stream.JsonToken
import com.google.gson.stream.JsonWriter
import okio.IOException

class IntegerGsonTypeAdapter : TypeAdapter<Number?>() {
    @Throws(IOException::class)
    override fun write(out: JsonWriter, value: Number?) {
        out.value(value)
    }

    @Throws(IOException::class)
    override fun read(`in`: JsonReader): Long? {
        print("THIS IS A TEST MESSAGE")
        val peek: JsonToken = `in`.peek()
        if (peek === JsonToken.NULL) {
            `in`.nextNull()
            return null
        }
        return try {
            val result: String = `in`.nextString()
            if ("" == result) {
                null
            } else result.toLong()
        } catch (e: java.lang.NumberFormatException) {
            null
        }catch (e: JsonSyntaxException){
            null
        }
    }
}