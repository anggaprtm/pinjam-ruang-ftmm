import React from 'react';
import { BookOpen, User, MapPin } from 'lucide-react';
import { Lecture } from '../types';
import GlassPanel from './GlassPanel';

const MOCK_LECTURES: Lecture[] = [
  {
    id: '1',
    courseCode: 'CS-301',
    title: 'Advanced Algorithms',
    time: '09:00 AM - 10:30 AM',
    instructor: 'Dr. Sarah Chen',
    status: 'Finished',
    room: 'Auditorium A'
  },
  {
    id: '2',
    courseCode: 'ENG-204',
    title: 'Thermodynamics II',
    time: '10:45 AM - 12:15 PM',
    instructor: 'Prof. J. Miller',
    status: 'Now',
    room: 'Lab 304'
  },
  {
    id: '3',
    courseCode: 'AI-405',
    title: 'Machine Learning Basics',
    time: '01:00 PM - 02:30 PM',
    instructor: 'Dr. A. Gupta',
    status: 'Upcoming',
    room: 'Hall B'
  },
  {
    id: '4',
    courseCode: 'PHY-102',
    title: 'Quantum Mechanics',
    time: '02:45 PM - 04:15 PM',
    instructor: 'Prof. R. Feynman',
    status: 'Upcoming',
    room: 'Room 101'
  },
    {
    id: '5',
    courseCode: 'MAT-201',
    title: 'Linear Algebra',
    time: '04:30 PM - 06:00 PM',
    instructor: 'Dr. L. Lovelace',
    status: 'Upcoming',
    room: 'Room 205'
  }
];

const LecturesPanel: React.FC = () => {
  return (
    <GlassPanel title="Today's Lectures" icon={<BookOpen className="w-6 h-6" />} className="h-full">
      <div className="space-y-4">
        {MOCK_LECTURES.map((lecture) => (
          <div 
            key={lecture.id} 
            className={`relative p-4 rounded-xl border transition-all duration-300 ${
              lecture.status === 'Now' 
                ? 'bg-electric-500/10 border-electric-500/50 shadow-[0_0_20px_rgba(6,182,212,0.15)]' 
                : 'bg-white/5 border-white/5 hover:bg-white/10'
            }`}
          >
            {lecture.status === 'Now' && (
              <div className="absolute top-4 right-4 flex items-center gap-1.5">
                <span className="relative flex h-2.5 w-2.5">
                  <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                  <span className="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500"></span>
                </span>
                <span className="text-xs font-bold text-green-400 uppercase tracking-wider">Now</span>
              </div>
            )}

            <div className="flex justify-between items-start mb-1">
              <span className={`text-xs font-bold px-2 py-0.5 rounded ${
                lecture.status === 'Now' ? 'bg-electric-500 text-white' : 'bg-white/10 text-white/60'
              }`}>
                {lecture.courseCode}
              </span>
            </div>
            
            <h3 className="text-lg font-bold text-white mb-1 line-clamp-1">{lecture.title}</h3>
            
            <div className="flex flex-col gap-1.5 mt-2">
                <div className="flex items-center gap-2 text-sm text-gray-300">
                    <User className="w-3.5 h-3.5 text-electric-400" />
                    <span>{lecture.instructor}</span>
                </div>
                 <div className="flex items-center gap-2 text-sm text-gray-300">
                    <MapPin className="w-3.5 h-3.5 text-electric-400" />
                    <span>{lecture.room}</span>
                </div>
                <div className="text-xs text-gray-500 mt-1 font-mono">
                    {lecture.time}
                </div>
            </div>
          </div>
        ))}
      </div>
    </GlassPanel>
  );
};

export default LecturesPanel;