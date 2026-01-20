import React from 'react';
import { Calendar, ArrowRight } from 'lucide-react';
import { Event } from '../types';
import GlassPanel from './GlassPanel';

const MOCK_EVENTS: Event[] = [
  {
    id: '1',
    title: 'The Future of Sustainable Engineering',
    speaker: 'Dr. Elena Vance, MIT',
    date: 'Today',
    time: '2:00 PM',
    location: 'Main Auditorium',
    image: 'https://picsum.photos/800/400?grayscale',
    isHighlight: true,
    category: 'Guest Lecture'
  },
  {
    id: '2',
    title: 'Spring Robotics Showcase',
    date: 'Tomorrow',
    time: '10:00 AM',
    location: 'Atrium B',
    isHighlight: false,
    category: 'Exhibition'
  },
  {
    id: '3',
    title: 'Graduate Networking Night',
    date: 'Fri, Oct 24',
    time: '6:00 PM',
    location: 'Student Center',
    isHighlight: false,
    category: 'Social'
  },
  {
    id: '4',
    title: 'AI Ethics Workshop',
    date: 'Mon, Oct 27',
    time: '1:00 PM',
    location: 'Room 404',
    isHighlight: false,
    category: 'Workshop'
  }
];

const EventsPanel: React.FC = () => {
  const highlightEvent = MOCK_EVENTS.find(e => e.isHighlight);
  const otherEvents = MOCK_EVENTS.filter(e => !e.isHighlight);

  return (
    <GlassPanel title="Upcoming Events" icon={<Calendar className="w-6 h-6" />} className="h-full">
      <div className="flex flex-col h-full gap-6">
        
        {/* Highlight Card */}
        {highlightEvent && (
          <div className="relative group overflow-hidden rounded-2xl aspect-video shrink-0">
             <img 
              src={highlightEvent.image} 
              alt={highlightEvent.title}
              className="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 opacity-60 mix-blend-overlay"
            />
            <div className="absolute inset-0 bg-gradient-to-t from-navy-900 via-navy-900/60 to-transparent" />
            
            <div className="absolute bottom-0 left-0 p-6 w-full">
              <span className="inline-block px-3 py-1 mb-3 text-xs font-bold tracking-wider text-white uppercase bg-electric-500 rounded-full">
                {highlightEvent.category}
              </span>
              <h3 className="text-3xl font-bold text-white mb-2 leading-tight max-w-lg">
                {highlightEvent.title}
              </h3>
              {highlightEvent.speaker && (
                <p className="text-lg text-electric-400 font-medium mb-1">{highlightEvent.speaker}</p>
              )}
               <p className="text-gray-300 flex items-center gap-2">
                 <span>{highlightEvent.time}</span>
                 <span className="w-1 h-1 bg-gray-500 rounded-full"></span>
                 <span>{highlightEvent.location}</span>
               </p>
            </div>
          </div>
        )}

        {/* List of other events */}
        <div className="flex-1 space-y-3 overflow-y-auto pr-2">
            <h4 className="text-sm text-gray-400 uppercase tracking-widest font-semibold mb-2 sticky top-0 bg-navy-900/90 backdrop-blur-sm py-2 z-10">
                Next Up
            </h4>
            {otherEvents.map(event => (
                <div key={event.id} className="group flex items-center p-4 rounded-xl bg-white/5 border border-white/5 hover:bg-white/10 transition-colors cursor-pointer">
                    <div className="flex flex-col items-center justify-center w-14 h-14 rounded-lg bg-white/5 border border-white/10 mr-4 group-hover:border-electric-500/50 transition-colors">
                        <span className="text-xs font-bold text-electric-400 uppercase">{event.date.split(' ')[0]}</span>
                        <span className="text-lg font-bold text-white">{event.date.split(' ')[1] || event.date}</span>
                    </div>
                    <div className="flex-1">
                        <h5 className="text-lg font-bold text-white group-hover:text-electric-400 transition-colors">{event.title}</h5>
                         <p className="text-sm text-gray-400">{event.time} &bull; {event.location}</p>
                    </div>
                    <ArrowRight className="w-5 h-5 text-gray-500 group-hover:text-white transition-transform group-hover:translate-x-1" />
                </div>
            ))}
        </div>

      </div>
    </GlassPanel>
  );
};

export default EventsPanel;