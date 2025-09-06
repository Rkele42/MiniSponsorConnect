"use client";
import { useState } from "react";
import Link from "next/link";
import { Button } from "@/components/ui/button";
import { Card } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";

export default function Page(){
  const IYZICO_LINK = process.env.NEXT_PUBLIC_IYZICO_LINK || "#";
  const [amount, setAmount] = useState("");
  const [desc, setDesc] = useState("");

  const onSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const a = Number(amount);
    const validAmount = !Number.isNaN(a) && a > 0;
    const validDesc = desc.trim().startsWith("@");
    if(!validAmount){ alert("Tutar pozitif bir sayı olmalı."); return; }
    if(!validDesc){ alert("Açıklama @ ile başlamalı (örn. @rkeles)."); return; }
    console.log("Bakiye Yükleme Bildirimi:", { amount: a, desc });
    alert("Bildirim alındı. Admin onayı ardından bakiyenize yansır.");
    setAmount(""); setDesc("");
  };

  return (
    <main className="p-6 max-w-xl mx-auto space-y-6">
      <h1 className="text-2xl font-bold">Cüzdan</h1>

      <Card className="p-4 space-y-3">
        <p className="text-sm font-medium">Kredi Yükle</p>
        <p className="text-xs opacity-70">
          ÖNEMLİ: Ödeme yaparken açıklamaya kullanıcı adınızı yazın (örn: @kullaniciadi).
        </p>
        <Link href={IYZICO_LINK} target="_blank">
          <Button className="w-full">iyzico ile Kredi Yükle</Button>
        </Link>
      </Card>

      <Card className="p-4 space-y-3">
        <p className="text-sm font-medium">Bakiye Yükleme Bildirimi</p>
        <form className="space-y-3" onSubmit={onSubmit}>
          <div>
            <label className="text-xs opacity-70">Tutar (TL)</label>
            <Input inputMode="numeric" value={amount} onChange={e=>setAmount(e.target.value)} placeholder="örn. 250" />
          </div>
          <div>
            <label className="text-xs opacity-70">Açıklama (@ ile başlayın)</label>
            <Textarea value={desc} onChange={e=>setDesc(e.target.value)} placeholder="@kullaniciadi" />
          </div>
          <Button type="submit" className="w-full">Bildirim Gönder</Button>
        </form>
      </Card>
    </main>
  );
}
