import { type FilterType, FILTER_TYPES } from '@astnext/common/models/Filter';
import axios, { type AxiosError, type AxiosPromise, type AxiosRequestConfig } from 'axios';
import axiosRetry from 'axios-retry';

import { ERROR_MESSAGES, RequestError } from '../common/errors/RequestError';
import { deleteCookie, getCookie } from '../common/helpers';

export * from 'axios';

const RETRY_STATUSES = [429, 408, /^5\d{2}$/];

async function getToken() {
  await new Promise((resolve) => setTimeout(resolve, 1000));
  return getCookie('jwt_token');
}

const request = axios.create({
  baseURL: new URL(process.env.MIX_ASTNEXT_APP_URL + '/api-astnext').href,
  withCredentials: true,
});

axiosRetry(request, {
  retries: 3,
  retryDelay: (retryCount) => {
    // console.log(`Retry attempt: ${retryCount}`);
    return Math.pow(2, retryCount) * 1000;
  },
  retryCondition: (error) => {
    return RETRY_STATUSES.reduce((acc, status) => {
      if (acc) return true;
      return Boolean(
        error &&
          error.response &&
          error.response.status &&
          error.response.status.toString().match(status as any)
      );
    }, false);
  },
});

request.interceptors.response.use(
  (res) => res,
  (e: AxiosError) => {
    const message = e.response?.data?.message || e.message;

    if (axios.isCancel(e)) {
      return Promise.reject(
        new RequestError({
          statusCode: 499,
          response: e.response,
          message: ERROR_MESSAGES.cancel,
        })
      );
    }

    if (e.response && e.response.status === 401) {
      deleteCookie('jwt_token');
      window.location.reload();
    }

    throw new RequestError({
      message,
      statusCode: e.response?.status,
      response: e.response,
    });
  }
);

const cancelable = {};

export default async function <T>({
  cancelPrevious,
  params = {},
  ...config
}: AxiosRequestConfig & { cancelPrevious?: boolean }) {
  const hasCohortFilters = Object.keys(params || {}).filter((param) =>
    FILTER_TYPES.includes(param as FilterType)
  );

  const requestConfig = {
    ...config,
    headers: {
      ...config.headers,
      Authorization: `Bearer ${(await getToken()) || ''}`,
    },
    params: {
      school_id: sessionStorage.getItem('school_id'),
      school_code: sessionStorage.getItem('school_code'),
      academic_year: sessionStorage.getItem('academic_year'),
      assessment_round: sessionStorage.getItem('assessment_round'),
      default_filters: !hasCohortFilters.length,
      ...params,
    },
  };

  if (cancelPrevious) {
    const key = `${config.method}-${config.url}`;
    const cancel = cancelable[key];
    if (cancel) cancel(ERROR_MESSAGES.cancel);
    return request({
      ...requestConfig,
      cancelToken: new axios.CancelToken((cancelRequest) => {
        cancelable[key] = cancelRequest;
      }),
    }) as AxiosPromise<T>;
  }

  return request(requestConfig) as AxiosPromise<T>;
}
