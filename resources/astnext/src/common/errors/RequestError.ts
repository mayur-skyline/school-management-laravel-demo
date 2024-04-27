import { AxiosResponse } from 'axios';

import AppError from './AppError';

export type ValidationErrors = Record<string, any>;

export const ERROR_MESSAGES = {
  cancel: 'CANCELLED_REQUEST',
};

export class RequestError extends AppError {
  public message: string;
  public statusCode?: number;
  public response?: AxiosResponse;
  public errors?: ValidationErrors;

  constructor(options: { message: string; statusCode?: number; response?: AxiosResponse }) {
    super(options.message);
    this.isExpected = false;
    this.message = options.message;
    this.statusCode = options.statusCode;
    this.response = options.response || null;
    this.errors = options?.response?.data?.errors || null;
  }

  isCancelled() {
    return this.statusCode === 499 || this.message === ERROR_MESSAGES.cancel;
  }

  /**
   * --------------------------------------------------
   * Returns an object with properties
   * --------------------------------------------------
   * @return {Object}
   * --------------------------------------------------
   */
  public toJson() {
    return { ...this };
  }
}
