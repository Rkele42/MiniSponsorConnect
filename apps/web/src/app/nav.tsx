"use client";
import Link from "next/link";
import { Home, Wallet, Tags, User } from "lucide-react";
export default function Nav(){
  const Item = ({href, children}:{href:string, children:React.ReactNode}) => (
    <Link href={href} className="inline-flex items-center gap-2 px-3 py-2 rounded-xl hover:bg-neutral-100 dark:hover:bg-neutral-900 transition">
      {children}
    </Link>
  );
  return (
    <nav className="sticky top-0 z-50 border-b bg-white/80 dark:bg-black/60 backdrop-blur">
      <div className="max-w-4xl mx-auto px-4 py-2 flex items-center justify-between">
        <Link href="/" className="font-bold">MiniSponsorConnect</Link>
        <div className="flex items-center gap-1 text-sm">
          <Item href="/(app)/feed"><Home size={18}/> Akış</Item>
          <Item href="/(app)/listings"><Tags size={18}/> İlanlar</Item>
          <Item href="/(app)/wallet"><Wallet size={18}/> Cüzdan</Item>
          <Item href="/(app)/profile"><User size={18}/> Profil</Item>
        </div>
      </div>
    </nav>
  );
}
