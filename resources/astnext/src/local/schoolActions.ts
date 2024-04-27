import { Pagination } from '../@types';
import { ISchoolAction } from '../common/models/SchoolAction';
import Student from '../common/models/Student';

export const studentActionPlans: { data: ISchoolAction[]; meta: Pagination } = {
  data: [
    {
      id: 1,
      student: new Student({
        id: 18,
        name: 'ALBERTALICE 6',
        gender: 'f',
        username: 'AlbertAlice6',
        password: 'A000001',
      }),
      type: 'STUDENT_ACTION_PLAN',
      label: 'Student Action Plan',
      risk: {
        type: 'POLAR_LOW_TRUST_OF_OTHERS',
        label: 'Polar Low Trust Of Others',
      },
      lead: 'John Smith',
      due: false,
      review_date: '2021-11-01',
    },
    {
      id: 2,
      student: new Student({
        id: 8,
        name: 'Testpupil3 10',
        gender: 'f',
        username: 'AlbertAlice6',
        password: 'A000001',
      }),
      type: 'STUDENT_ACTION_PLAN',
      label: 'Student Action Plan',
      risk: {
        type: 'POLAR_LOW_TRUST_OF_OTHERS',
        label: 'Polar Low Trust Of Others',
      },
      lead: 'John Smith',
      due: false,
      review_date: '2021-11-01',
    },
  ],
  meta: {
    total: 3,
    total_page: 1,
    current_page: 0,
    per_page: 15,
    current_page_size: 5,
  },
};
export const monitorComments: { data: ISchoolAction[]; meta: Pagination } = {
  data: [
    {
      id: 1,
      student: new Student({
        id: 18,
        name: 'ALBERTALICE 6',
        gender: 'f',
        username: 'AlbertAlice6',
        password: 'A000001',
      }),
      type: 'STUDENT_ACTION_PLAN',
      label: 'Student Action Plan',
      risk: {
        type: 'POLAR_LOW_TRUST_OF_OTHERS',
        label: 'Polar Low Trust Of Others',
      },
      lead: 'John Smith',
      due: false,
      review_date: '2021-11-01',
    },
    {
      id: 2,
      student: new Student({
        id: 8,
        name: 'Testpupil3 10',
        gender: 'f',
        username: 'AlbertAlice6',
        password: 'A000001',
      }),
      type: 'STUDENT_ACTION_PLAN',
      label: 'Student Action Plan',
      risk: {
        type: 'POLAR_LOW_TRUST_OF_OTHERS',
        label: 'Polar Low Trust Of Others',
      },
      lead: 'John Smith',
      due: false,
      review_date: '2021-11-01',
    },
  ],
  meta: {
    total: 3,
    total_page: 1,
    current_page: 0,
    per_page: 15,
    current_page_size: 5,
  },
};
export const cohortActionPlans: { data: ISchoolAction[]; meta: Pagination } = {
  data: [
    {
      id: 1,
      student: new Student({
        id: 18,
        name: 'ALBERTALICE 6',
        gender: 'f',
        username: 'AlbertAlice6',
        password: 'A000001',
      }),
      type: 'STUDENT_ACTION_PLAN',
      label: 'Student Action Plan',
      risk: {
        type: 'POLAR_LOW_TRUST_OF_OTHERS',
        label: 'Polar Low Trust Of Others',
      },
      lead: 'John Smith',
      due: false,
      review_date: '2021-11-01',
    },
    {
      id: 2,
      student: new Student({
        id: 8,
        name: 'Testpupil3 10',
        gender: 'f',
        username: 'AlbertAlice6',
        password: 'A000001',
      }),
      type: 'STUDENT_ACTION_PLAN',
      label: 'Student Action Plan',
      risk: {
        type: 'POLAR_LOW_TRUST_OF_OTHERS',
        label: 'Polar Low Trust Of Others',
      },
      lead: 'John Smith',
      due: false,
      review_date: '2021-11-01',
    },
  ],
  meta: {
    total: 3,
    total_page: 1,
    current_page: 0,
    per_page: 15,
    current_page_size: 5,
  },
};
export const familySignposts: { data: ISchoolAction[]; meta: Pagination } = {
  data: [
    {
      id: 1,
      student: new Student({
        id: 18,
        name: 'ALBERTALICE 6',
        gender: 'f',
        username: 'AlbertAlice6',
        password: 'A000001',
      }),
      type: 'STUDENT_ACTION_PLAN',
      label: 'Student Action Plan',
      risk: {
        type: 'POLAR_LOW_TRUST_OF_OTHERS',
        label: 'Polar Low Trust Of Others',
      },
      lead: 'John Smith',
      due: false,
      review_date: '2021-11-01',
    },
    {
      id: 2,
      student: new Student({
        id: 8,
        name: 'Testpupil3 10',
        gender: 'f',
        username: 'AlbertAlice6',
        password: 'A000001',
      }),
      type: 'STUDENT_ACTION_PLAN',
      label: 'Student Action Plan',
      risk: {
        type: 'POLAR_LOW_TRUST_OF_OTHERS',
        label: 'Polar Low Trust Of Others',
      },
      lead: 'John Smith',
      due: false,
      review_date: '2021-11-01',
    },
  ],
  meta: {
    total: 3,
    total_page: 1,
    current_page: 0,
    per_page: 15,
    current_page_size: 5,
  },
};
export const groupActionPlans: { data: ISchoolAction[]; meta: Pagination } = {
  data: [
    {
      id: 1,
      student: new Student({
        id: 18,
        name: 'ALBERTALICE 6',
        gender: 'f',
        username: 'AlbertAlice6',
        password: 'A000001',
      }),
      type: 'STUDENT_ACTION_PLAN',
      label: 'Student Action Plan',
      risk: {
        type: 'POLAR_LOW_TRUST_OF_OTHERS',
        label: 'Polar Low Trust Of Others',
      },
      lead: 'John Smith',
      due: false,
      review_date: '2021-11-01',
    },
    {
      id: 2,
      student: new Student({
        id: 8,
        name: 'Testpupil3 10',
        gender: 'f',
        username: 'AlbertAlice6',
        password: 'A000001',
      }),
      type: 'STUDENT_ACTION_PLAN',
      label: 'Student Action Plan',
      risk: {
        type: 'POLAR_LOW_TRUST_OF_OTHERS',
        label: 'Polar Low Trust Of Others',
      },
      lead: 'John Smith',
      due: false,
      review_date: '2021-11-01',
    },
  ],
  meta: {
    total: 3,
    total_page: 1,
    current_page: 0,
    per_page: 15,
    current_page_size: 5,
  },
};
