import { type NonFunctionProperties } from '../../../../astnext/src/@types';
import Model from '../../../../astnext/src/common/models/Model';
import request from '../../../../astnext/src/services/request.service';

export type IForgotData = NonFunctionProperties<Partial<ForgotPassword>>;

export default class ForgotPassword extends Model {
  success: string;
  reset_link: string;
  old_reset_link: string;
  error: string;
  string_error: string;
  session: {
    school_id: string;
    school_name: string;
    school_code: string;
    is_school_auth: string;
  };
  constructor(params: Partial<IForgotData>) {
    super(params);
  }

  static async getForgotData(reqData: { school_id: string; username: string }) {
    return request<IForgotData>({
      method: 'POST',
      url: `forgot-password`,
      data: reqData,
    }).then(({ data }) => {
      return data;
    });
  }
  static async getForgotstep2Data(reqData: {
    string: string;
    new_password: string;
    confirm_password: string;
  }) {
    return request<IForgotData>({
      method: 'POST',
      url: `forgot-password-step2`,
      data: reqData,
    }).then(({ data }) => {
      return data;
    });
  }

  static async sendStudentLoginDetails(username: string) {
    return request<IForgotData>({
      method: 'POST',
      url: `send-student-login-details`,
      data: {
        username,
      },
    }).then(({ data }) => {
      return data;
    });
  }
}
