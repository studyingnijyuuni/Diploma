<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function profile(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'Username' => $user->Username,
            'Email' => $user->Email,
            'TelegramID' => $user->TelegramID,
            'TelegramConfirmationCode' => $user->TelegramConfirmationCode
        ]);
    }

    public function favorites(Request $request)
    {
        $favorites = $request->user()->favoriteMangas()
            ->select('mangas.MangaID', 'Title', 'Image', 'LastReleaseDate', 'UpcomingReleaseDate', 'SourceLink')
            ->orderBy('UpcomingReleaseDate', 'DESC')
            ->get();

        return response()->json([
            'mangas' => $favorites,
            'message' => 'Success retrieving!'
        ]);
    }

    public function followManga(Request $request)
    {
        $request->validate(['MangaID' => 'required|exists:mangas,MangaID']);

        $request->user()->favoriteMangas()->syncWithoutDetaching([$request->MangaID]);

        return response()->json(['message' => 'Followed successfully!']);
    }

    public function unfollowManga($mangaId, Request $request)
    {
        $request->user()->favoriteMangas()->detach($mangaId);

        return response()->json(['message' => 'Unfollowed successfully!']);
    }

    public function setNotifications(Request $request, $mangaId)
    {
        $request->validate(['isNotify' => 'required|boolean']);

        $request->user()->favoriteMangas()->updateExistingPivot($mangaId, [
            'IsEmailNotificationsOn' => $request->isNotify,
            'IsTelegramNotificationsOn' => $request->isNotify,
        ]);

        $status = $request->isNotify ? 'true' : 'false';
        return response()->json(['message' => "Set Notifications to {$status} successfully!"]);
    }

    public function updateEmail(Request $request)
    {
        $request->validate([
            'emailAddress' => 'required|email|unique:users,Email,' . $request->user()->UserID . ',UserID'
        ]);

        $request->user()->update([
            'Email' => $request->emailAddress
        ]);

        return response()->json(['message' => 'Changed email successfully!']);
    }

    public function generateTelegramCode(Request $request)
    {
        $unique_code = bin2hex(random_bytes(5));

        $request->user()->update([
            'TelegramConfirmationCode' => $unique_code
        ]);

        return response()->json([
            'code' => $unique_code, 
            'message' => 'Telegram is ready to be linked!'
        ]);
    }

    public function unlinkTelegram(Request $request)
    {
        $request->user()->update([
            'TelegramID' => null,
            'TelegramConfirmationCode' => null 
        ]);

        return response()->json(['message' => 'Unlinked Telegram successfully!']);
    }
}