import { NonFunctionProperties, PageParams, Pagination } from '../../@types';
import request from '../../services/request.service';
import { FilterParams } from './Filter';
import Model from './Model';
import Risk from './Risk';
import Student from './Student';

export type ISafeguarding = NonFunctionProperties<Partial<Safeguarding>>;

export type AssessmentData = {
  risk_count: number;
  year: number;
  date: string;
  risks: Risk[];
};

export type SafeguardingVariant = {
  current_assessment: AssessmentData;
  past_assessments: AssessmentData[];
};

export default class Safeguarding extends Model {
  private static url = 'safeguarding';

  constructor(params: ISafeguarding) {
    super(params);
  }

  student: Student;

  assessment: SafeguardingVariant;

  /**
   * ------------------------------------------------------------
   * Returns Paginated list of school actions for defined page or
   * type
   * ------------------------------------------------------------
   * @param page
   * @param params
   * ------------------------------------------------------------
   */
  static list(params: FilterParams & PageParams = {}) {
    return request<{
      IN_SCHOOL: ISafeguarding[];
      OUT_OF_SCHOOL: ISafeguarding[];
      meta: Pagination;
    }>({
      method: 'GET',
      url: this.url,
      cancelPrevious: true,
      params,
    }).then(({ data: { IN_SCHOOL, OUT_OF_SCHOOL, meta } }) => {
      return {
        safeguarding: {
          IN_SCHOOL: IN_SCHOOL.map((safeguarding) => new Safeguarding(safeguarding)),
          OUT_OF_SCHOOL: OUT_OF_SCHOOL.map((safeguarding) => new Safeguarding(safeguarding)),
        },
        pagination: meta,
      };
    });
  }
}
