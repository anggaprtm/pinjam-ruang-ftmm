import React from 'react';
import { Users, Clock } from 'lucide-react';
import { Meeting } from '../types';
import GlassPanel from './GlassPanel';

const MOCK_MEETINGS: Meeting[] = [
  { id: '1', room: '304', title: 'Dept Meeting', time: '11:00 AM', status: 'Occupied' },
  { id: '2', room: '401', title: 'Thesis Defense', time: '01:00 PM', status: 'Reserved' },
  { id: '3', room: '202', title: 'Study Group A', time: '02:00 PM', status: 'Available' },
  { id: '4', room: '105', title: 'Faculty Board', time: '03:30 PM', status: 'Reserved' },
  { id: '5', room: '308', title: 'Lab Maintenance', time: 'All Day', status: 'Occupied' },
  { id: '6', room: '205', title: 'IEEE Student Chapter', time: '05:00 PM', status: 'Reserved' },
];

const MeetingsPanel: React.FC = () => {
  return (
    <GlassPanel title="Exams & Meetings" icon={<Users className="w-6 h-6" />} className="h-full">
      <div className="grid gap-3">
        {MOCK_MEETINGS.map((meeting) => (
            <div key={meeting.id} className="flex flex-col p-4 rounded-xl bg-white/5 border border-white/5">
                <div className="flex justify-between items-center mb-2">
                    <span className="text-2xl font-bold text-white font-mono">{meeting.room}</span>
                    <span className={`px-2 py-1 rounded-md text-[10px] uppercase font-bold tracking-wider ${
                        meeting.status === 'Occupied' ? 'bg-red-500/20 text-red-400 border border-red-500/30' :
                        meeting.status === 'Available' ? 'bg-green-500/20 text-green-400 border border-green-500/30' :
                        'bg-yellow-500/20 text-yellow-400 border border-yellow-500/30'
                    }`}>
                        {meeting.status}
                    </span>
                </div>
                <div className="text-sm font-medium text-gray-200 truncate">{meeting.title}</div>
                 <div className="flex items-center gap-2 mt-2 text-xs text-gray-500">
                    <Clock className="w-3 h-3" />
                    {meeting.time}
                 </div>
            </div>
        ))}

        {/* Quick Stats at bottom */}
        <div className="mt-6 p-4 rounded-xl bg-gradient-to-r from-electric-500/10 to-transparent border border-electric-500/20">
            <h4 className="text-xs font-bold text-electric-400 uppercase tracking-widest mb-2">Room Availability</h4>
            <div className="flex justify-between items-end">
                <div>
                     <span className="text-3xl font-bold text-white">4</span>
                     <span className="text-xs text-gray-400 ml-1">Open</span>
                </div>
                 <div className="h-1 flex-1 mx-4 bg-white/10 rounded-full overflow-hidden">
                    <div className="h-full bg-electric-500 w-[35%]"></div>
                 </div>
                 <div className="text-right">
                    <span className="text-lg font-bold text-white/60">12</span>
                     <span className="text-xs text-gray-400 ml-1">Total</span>
                 </div>
            </div>
        </div>
      </div>
    </GlassPanel>
  );
};

export default MeetingsPanel;