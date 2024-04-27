import { type NonFunctionProperties } from '../../@types';
import request from '../../services/request.service';
import Model from './Model';

export type IPSHE = NonFunctionProperties<Partial<PSHE>>;

export type Resource = {
  id: number;
  size?: string;
  title: string;
  type: string;
  url: string;
};

export type LessonCategories =
  | 'lesson_plan'
  | 'powerpoint'
  | 'display_text'
  | 'take_home_resource'
  | 'poster'
  | 'pupil_resource'
  | 'family_footprints_challenge'
  | 'footprints_for_parents'
  | 'footprints_for_teachers'
  | 'cut_out'
  | 'scheme_of_work'
  | 'display_resource'
  | 'family_footprints'
  | 'teacher_resource'
  | 'stage_2_teacher_training';

export type SupportingResourceCategories =
  | 'teaching_training'
  | 'scheme_of_work'
  | 'preparing_your_footprints_display_board'
  | 'footprints_for_teachers'
  | 'display_board_materials'
  | 'family_footprints';

export type Curriculum = {
  title: string;
  description: string;
  status: 'active' | 'inactive';
  url?: string;
  image: string;
  age: string;
};

export type PSHEData = {
  footprint: Curriculum;
  take_the_wheel: Curriculum;
  usteer: Curriculum;
};
export type LessonResource = {
  [key in LessonCategories]?: {
    title: string;
    type: string;
    resources: {
      id: number;
      size?: string;
      url: string;
    }[];
  };
};

export type Lesson = {
  id?: number;
  title: string;
  description: string;
  resources: LessonResource;
};

export type Lessons = {
  lessons: Lesson[];
};

export type Footprint = {
  footprints: {
    id?: number;
    title: string;
    description: string;
  }[];
  videos: {
    id?: number;
    title: string;
    url: string;
    duration: string;
  }[];
};

export type SupportingResource = {
  title: string;
  resources: Resource[];
};

export type SupportingResources = {
  supporting_resources: {
    [key in SupportingResourceCategories]?: SupportingResource;
  };
};

export default class PSHE extends Model {
  constructor(params: Partial<IPSHE>) {
    super(params);
  }

  static async getLesson(url) {
    return request<Lessons>({
      method: 'GET',
      url: url,
    }).then(({ data: data }) => {
      return data;
    });
  }

  static async getCurriculum(url) {
    return request<Footprint>({
      method: 'GET',
      url: url,
    }).then(({ data: data }) => {
      return data;
    });
  }

  static async getSupportingResources(url) {
    return request<SupportingResources>({
      method: 'GET',
      url: url,
    }).then(({ data: data }) => {
      return data;
    });
  }

  static async getPSHEData(url) {
    return request<PSHEData>({
      method: 'GET',
      url: url,
    }).then(({ data: data }) => {
      return data;
    });
  }
}
