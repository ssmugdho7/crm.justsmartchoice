package com.divesh.perfex.core.helpers

import android.content.Context
import android.content.SharedPreferences
import com.divesh.perfex.core.api.ApiInterface
import com.divesh.perfex.core.api.gson_type_adapters.IntegerGsonTypeAdapter
import com.divesh.perfex.core.constants.Constants.Companion.API_URL
import com.divesh.perfex.core.constants.Constants.Companion.AUTHENTICATION_KEY
import com.divesh.perfex.core.constants.Constants.Companion.AUTHENTICATION_VALUE
import com.divesh.perfex.customers.data.repositories.CustomersApiRepository
import com.divesh.perfex.dashboard.data.repositories.DashboardApiRepository
import com.divesh.perfex.leads.data.repositories.LeadsApiRepository
import com.divesh.perfex.login.data.repositories.LoginApiRepository
import com.divesh.perfex.notifications.data.repositories.NotificationsApiRepository
import com.divesh.perfex.sales.data.repositories.SalesApiRepository
import com.divesh.perfex.support.data.repositories.SupportApiRepository
import com.google.gson.GsonBuilder
import dagger.Module
import dagger.Provides
import dagger.hilt.InstallIn
import dagger.hilt.android.qualifiers.ApplicationContext
import dagger.hilt.components.SingletonComponent
import okhttp3.OkHttpClient
import okhttp3.logging.HttpLoggingInterceptor
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory
import java.util.concurrent.TimeUnit
import javax.inject.Singleton


@Module
@InstallIn(SingletonComponent::class)
object ModulesProvider {
    @Singleton
    @Provides
    fun provideSharedPreference(@ApplicationContext context: Context): SharedPreferences {
        return context.getSharedPreferences(
            "${com.divesh.perfex.core.constants.Constants.packagePreFix}_preferences",
            Context.MODE_PRIVATE
        )
    }

    @Singleton
    @Provides
    fun providesHttpLoggingInterceptor() = HttpLoggingInterceptor()
        .apply {
            level = HttpLoggingInterceptor.Level.BODY
        }

    @Singleton
    @Provides
    fun providesOkHttpClient(httpLoggingInterceptor: HttpLoggingInterceptor): OkHttpClient =
        OkHttpClient
            .Builder()
            .addInterceptor(httpLoggingInterceptor)
            .connectTimeout(30, TimeUnit.SECONDS)
            .readTimeout(30, TimeUnit.SECONDS)
            .followRedirects(true)
            .followSslRedirects(true)
            .addInterceptor { chain ->
                val newRequest = chain.request().newBuilder()
                    .addHeader(AUTHENTICATION_KEY, AUTHENTICATION_VALUE)
                    .build()
                chain.proceed(newRequest)
            }
            .build()

    @Singleton
    @Provides
    fun provideRetrofit(okHttpClient: OkHttpClient): Retrofit {
        val gson = GsonBuilder()
            .registerTypeHierarchyAdapter(
                Long::class.java,
                IntegerGsonTypeAdapter()
            ).create()
        return Retrofit.Builder()
            .addConverterFactory(GsonConverterFactory.create(gson))
            .baseUrl(API_URL)
            .client(okHttpClient)
            .build()
    }
    /*@Singleton
    @Provides
    fun provideRetrofit1(okHttpClient: OkHttpClient): Retrofit = Retrofit.Builder()
        .addConverterFactory(GsonConverterFactory.create())
        .baseUrl(API_URL)
        .client(okHttpClient)
        .build()*/

    @Singleton
    @Provides
    fun provideApiService(retrofit: Retrofit): ApiInterface =
        retrofit.create(ApiInterface::class.java)

    @Singleton
    @Provides
    fun providesRepository(apiInterface: ApiInterface) = LoginApiRepository(apiInterface)

    @Singleton
    @Provides
    fun providesLeadsApiRepository(apiInterface: ApiInterface) = LeadsApiRepository(apiInterface)

    @Singleton
    @Provides
    fun providesCustomersApiRepository(apiInterface: ApiInterface) =
        CustomersApiRepository(apiInterface)

    @Singleton
    @Provides
    fun providesSupportApiRepository(apiInterface: ApiInterface) =
        SupportApiRepository(apiInterface)

    @Singleton
    @Provides
    fun providesDashboardApiRepository(apiInterface: ApiInterface) =
        DashboardApiRepository(apiInterface)

    @Singleton
    @Provides
    fun providesNotificationsApiRepository(apiInterface: ApiInterface) =
        NotificationsApiRepository(apiInterface)

    @Singleton
    @Provides
    fun providesSalesApiRepository(apiInterface: ApiInterface) = SalesApiRepository(apiInterface)
}