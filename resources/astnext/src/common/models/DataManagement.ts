import request from '../../services/request.service';
import Model from './Model';

export type Signpost = {
  id: number;
  signpost: string;
};

export default class DataManagement extends Model {
  static async getTwoFactorAuth() {
    return request<{ status: number }>({
      url: 'factor-auth/school-factor-auth',
    }).then(({ data }) => {
      return data.status;
    });
  }

  static async setTwoFactorAuth(status: number) {
    return request({
      url: 'factor-auth/school-factor-auth',
      method: 'POST',
      data: {
        status,
      },
    }).then(({ data }) => {
      return data;
    });
  }
}
